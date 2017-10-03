#!groovy

pipeline {
  agent any

  parameters {
    string(name: 'CVHR_BRANCH', defaultValue: '', description: 'Default build branch of CiviHR to build site using CiviCRM-Buildkit')
    string(name: 'CVHR_BUILDNAME', defaultValue: "hr17-dev_$BRANCH_NAME", description: 'CiviHR site name')
    booleanParam(name: 'DESTORY_SITE', defaultValue: true, description: 'Destroy built site after build finish')
  }

  environment {
    WEBROOT = "/opt/buildkit/build/${params.CVHR_BUILDNAME}"
    DRUPAL_SITES_ALL = "$WEBROOT/sites/all"
    DR_MODU_ROOT = "$DRUPAL_SITES_ALL/modules"
    DR_THEME_ROOT = "$DRUPAL_SITES_ALL/themes"
    CVCRM_EXT_ROOT = "$DR_MODU_ROOT/civicrm/tools/extensions"
    WEBURL = "http://jenkins.compucorp.co.uk:8900"
    ADMIN_PASS = credentials('CVHR_ADMIN_PASS')
  }

  stages {
    stage('Pre-tasks execution') {
      steps {
        // Print all Environment variables
        sh 'printenv | sort'

        // Destroy existing site
        sh "civibuild destroy ${params.CVHR_BUILDNAME} || true"

        // Test build tools
        sh 'amp test'
      }
    }

    stage('Build site') {
      steps {
        script {
          // Setup Building branch with
          // CVHR_BRANCH parameter from manually build with parameter
          // or PR target branch (CHANGE_TARGET) from building pull request
          // or branch (BRANCH_NAME) from building individual branch
          def buildBranch = params.CVHR_BRANCH != '' ? params.CVHR_BRANCH : env.CHANGE_TARGET != null ? env.CHANGE_TARGET : env.BRANCH_NAME != null ? env.BRANCH_NAME : 'staging'

          // Build site with CV Buildkit
          sh "civibuild create ${params.CVHR_BUILDNAME} --type hr16 --civi-ver 4.7.22 --hr-ver ${buildBranch} --url $WEBURL --admin-pass $ADMIN_PASS"

          // Change git remote of civihr ext to support dev version of Jenkins pipeline
          changeCivihrGitRemote()

          // Get repos & branch name
          def prBranch = env.CHANGE_BRANCH
          def envBranch = env.CHANGE_TARGET
          if (prBranch != null && prBranch.startsWith("hotfix-")) {
            envBranch = 'master'
          }

          if (prBranch) {
            checkoutPrBranchInCiviHRRepos(prBranch)
            mergeEnvBranchInAllRepos(envBranch)
          }

          sh """
              cd $WEBROOT
              drush features-revert civihr_employee_portal_features -y
              drush features-revert civihr_default_permissions -y
              drush updatedb -y
              drush cvapi extenion.upgrade -y
              drush cc all
              drush cc civicrm
            """
        }
      }
    }

    /* Testing PHP */
    stage('Test PHP') {
      steps {
        script {
          for (extension in listCivihrExtensions()) {
            testPHPUnit(extension)
          }
        }
      }
      post {
        always {
          step([
            $class: 'XUnitBuilder',
            thresholds: [
              [
                $class: 'FailedThreshold',
                failureNewThreshold: '5',
                failureThreshold: '5',
                unstableNewThreshold: '1',
                unstableThreshold: '1'
              ],
              [
                $class: 'SkippedThreshold',
                failureNewThreshold: '0',
                failureThreshold: '0',
                unstableNewThreshold: '0',
                unstableThreshold: '0'
              ]
            ],
            tools: [
              [
                $class: 'JUnitType',
                pattern: 'reports/phpunit/*.xml'
              ]
            ]
          ])
        }
      }
    }

  /* Testing JS */
  // TODO: Execute test and Generate report without stop on fail
    stage('Testing JS: Install NPM in parallel') {
      steps {
        script {
          def extensionTestings = [:]

          // Install NPM jobs
          for (extension in listCivihrExtensions()) {
            extensionTestings[extension] = {
              installNPM(extension)
            }
          }
          // Running install NPM jobs in parallel
          parallel extensionTestings
        }
      }
    }

    stage('Testing JS: Test JS in sequent') {
      steps {
        script {
          // Testing JS in sequent
          for (extension in listCivihrExtensions) {
            testJS(extension)
          }
        }
      }
      post {
        always {
          step([
            $class: 'XUnitBuilder',
            thresholds: [
              [
                $class: 'FailedThreshold',
                failureNewThreshold: '5',
                failureThreshold: '5',
                unstableNewThreshold: '1',
                unstableThreshold: '1'
              ],
              [
                $class: 'SkippedThreshold',
                failureNewThreshold: '0',
                failureThreshold: '0',
                unstableNewThreshold: '0',
                unstableThreshold: '0'
              ]
            ],
            tools: [
              [
                $class: 'JUnitType',
                pattern: 'reports/js-karma/*.xml'
              ]
            ]
          ])
        }
      }
    }
  }

  post {
    always {
      // Destroy built site
      script {
        if (params.DESTORY_SITE == true) {
          echo 'Destroying built site...'
          sh "civibuild destroy ${params.CVHR_BUILDNAME} || true"
        }
      }
    }
  }
}

/*
 *  Change URL Git remote of civihr main repositry to the URL where configured by Jenkins project
 */
def changeCivihrGitRemote() {
  def pulledCvhrRepo = sh(returnStdout: true, script: "cd $WORKSPACE; git remote -v | grep fetch | awk '{print \$2}'").trim()

  echo 'Changing Civihr git URL..'

  sh """
    cd $CVCRM_EXT_ROOT/civihr
    git remote set-url origin ${pulledCvhrRepo}
    git fetch --all
  """
}

def checkoutPrBranchInCiviHRRepos(String branch) {
  echo 'Checking out CiviHR repos..'

  for (repo in listCivihrGitRepoPath()) {
    try {
        sh """
          cd ${repo}
          git checkout ${branch}
        """
    } catch (err) {}
  }
}

def mergeEnvBranchInAllRepos(String envBranch) {
  echo 'Merging env branch'

  for (repo in listCivihrGitRepoPath()) {
    try {
        sh """
          cd ${repo}
          git merge origin/${envBranch} --no-edit
        """
    } catch (err) {}
  }
}

/*
 * Execute PHPUnit testing
 * params: extensionName
 */
def testPHPUnit(String extensionName) {
  def extensionShortName = extensionName.tokenize('.')[-1]

  echo "PHPUnit testing: ${extensionShortName}"

  sh """
    cd $CVCRM_EXT_ROOT/civihr/${extensionName}
    phpunit4 \
      --log-junit $WORKSPACE/reports/phpunit/result-phpunit_${extensionShortName}.xml \
      || true
  """
}

/*
 * Installk JS Testing
 * params: extensionName
 */
def installNPM(String extensionName) {
  sh """
    cd $CVCRM_EXT_ROOT/civihr/${extensionName}
    npm install || true
  """
}

/*
 * Execute JS Testing
 * params: extensionName
 */
def testJS(String extensionName) {
  echo "JS Testing ${extensionName.tokenize('.')[-1]}"

  sh """
    cd $CVCRM_EXT_ROOT/civihr/${extensionName}
    gulp test || true
  """
}

/*
 * Get a list of CiviHR repository
 * https://compucorp.atlassian.net/wiki/spaces/PCHR/pages/68714502/GitHub+repositories
 */
def listCivihrGitRepoPath() {
  return [
    "$CVCRM_EXT_ROOT/civihr",
    "$CVCRM_EXT_ROOT/civihr_tasks",
    "$CVCRM_EXT_ROOT/org.civicrm.shoreditch",
    "$CVCRM_EXT_ROOT/org.civicrm.styleguide",
    "$DR_MODU_ROOT/civihr-custom",
    "$DR_THEME_ROOT/civihr_employee_portal_theme"
  ]
}

/*
 * Get a list of enabled CiviHR extensions
 */
def listCivihrExtensions() {
  return [
    'uk.co.compucorp.civicrm.hrcore',
    'com.civicrm.hrjobroles',
  ]
}
