#!groovy

pipeline {
  agent any

  parameters {
    string(name: 'CIVIHR_BRANCH', defaultValue: '', description: 'Default build branch of CiviHR to build site using CiviCRM-Buildkit')
    string(name: 'CIVIHR_BUILDNAME', defaultValue: "hr17-dev_$BRANCH_NAME", description: 'CiviHR site name')
    booleanParam(name: 'DESTROY_SITE', defaultValue: true, description: 'Destroy built site after build finish')
  }

  environment {
    WEBROOT = "/opt/buildkit/build/${params.CIVIHR_BUILDNAME}"
    DRUPAL_SITES_ALL = "$WEBROOT/sites/all"
    DRUPAL_MODULES_ROOT = "$DRUPAL_SITES_ALL/modules"
    DRUPAL_THEMES_ROOT = "$DRUPAL_SITES_ALL/themes"
    CIVICRM_EXT_ROOT = "$DRUPAL_MODULES_ROOT/civicrm/tools/extensions"
    WEBURL = "http://jenkins.compucorp.co.uk:8900"
    ADMIN_PASS = credentials('CVHR_ADMIN_PASS')
  }

  stages {
    stage('Pre-tasks execution') {
      steps {
        // Print all Environment variables
        sh 'printenv | sort'

        // Destroy existing site
        sh "civibuild destroy ${params.CIVIHR_BUILDNAME} || true"

        // Test build tools
        sh 'amp test'
      }
    }

    stage('Build site') {
      steps {
        script {
          // Setup Building branch with
          // CIVIHR_BRANCH parameter from manually build with parameter
          // or PR target branch (CHANGE_TARGET) from building pull request
          // or branch (BRANCH_NAME) from building individual branch
          def buildBranch = params.CIVIHR_BRANCH != '' ? params.CIVIHR_BRANCH : env.CHANGE_TARGET != null ? env.CHANGE_TARGET : env.BRANCH_NAME != null ? env.BRANCH_NAME : 'staging'

          // Build site with CV Buildkit
          sh "civibuild create ${params.CIVIHR_BUILDNAME} --type hr16 --civi-ver 4.7.22 --hr-ver ${buildBranch} --url $WEBURL --admin-pass $ADMIN_PASS"

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
              drush cvapi extension.upgrade -y
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
            if (extension.hasPHPTests) {
              testPHPUnit(extension)
            }
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
                failureNewThreshold: '1',
                failureThreshold: '1',
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
            if(!extension.hasJSTests) {
              continue;
            }

            extensionTestings[extension.shortName] = {
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
            if(extension.hasJSTests) {
              testJS(extension)
            }
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
                failureNewThreshold: '1',
                failureThreshold: '1',
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
        if (params.DESTROY_SITE == true) {
          echo 'Destroying built site...'
          sh "civibuild destroy ${params.CIVIHR_BUILDNAME} || true"
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
    cd $CIVICRM_EXT_ROOT/civihr
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
 * params: extension
 */
def testPHPUnit(java.util.LinkedHashMap extension) {
  echo "PHPUnit testing: ${extension.name}"

  sh """
    cd $CIVICRM_EXT_ROOT/civihr/${extension.folder}
    phpunit4 --log-junit $WORKSPACE/reports/phpunit/result-phpunit_${extension.shortName}.xml || true
  """
}

/*
 * Installk JS Testing
 * params: extension
 */
def installNPM(java.util.LinkedHashMap extension) {
  sh """
    cd $CIVICRM_EXT_ROOT/civihr/${extension.folder}
    npm install || true
  """
}

/*
 * Execute JS Testing
 * params: extension
 */
def testJS(java.util.LinkedHashMap extension) {
  echo "JS Testing ${extension.name}"

  sh """
    cd $CIVICRM_EXT_ROOT/civihr/${extension.folder}
    gulp test || true
  """
}

/*
 * Get a list of CiviHR repository
 * https://compucorp.atlassian.net/wiki/spaces/PCHR/pages/68714502/GitHub+repositories
 */
def listCivihrGitRepoPath() {
  return [
    "$CIVICRM_EXT_ROOT/civihr",
    "$CIVICRM_EXT_ROOT/civihr_tasks",
    "$CIVICRM_EXT_ROOT/org.civicrm.shoreditch",
    "$CIVICRM_EXT_ROOT/org.civicrm.styleguide",
    "$DRUPAL_MODULES_ROOT/civihr-custom",
    "$DRUPAL_THEMES_ROOT/civihr_employee_portal_theme"
  ]
}

/*
 * Get a list of enabled CiviHR extensions
 */
def listCivihrExtensions() {
  return [
    [
      name: 'Job Roles',
      shortName: 'hrjobroles',
      folder: 'com.civicrm.hrjobroles',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'Contacts Access Rights',
      shortName: 'contactaccessrights',
      folder: 'contactaccessrights',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'Job Contracts',
      shortName: 'hrjobcontract',
      folder: 'hrjobcontract',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'Recruitment',
      shortName: 'hrrecruitment',
      folder: 'hrrecruitment',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'Reports',
      shortName: 'hrreport',
      folder: 'hrreport',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'HR UI',
      shortName: 'hrui',
      folder: 'hrui',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'HR Visa',
      shortName: 'hrvisa',
      folder: 'hrvisa',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'HRCore',
      shortName: 'hrcore',
      folder: 'uk.co.compucorp.civicrm.hrcore',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'Leave and Absences',
      shortName: 'hrleaveandabsences',
      folder: 'uk.co.compucorp.civicrm.hrleaveandabsences',
      hasJSTests: false,
      hasPHPTests: true
    ],
    [
      name: 'Sample Data',
      shortName: 'hrsampledata',
      folder: 'uk.co.compucorp.civicrm.hrsampledata',
      hasJSTests: false,
      hasPHPTests: true
    ]
  ]
}
