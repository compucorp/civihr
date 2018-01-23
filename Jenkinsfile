#!groovy

pipeline {
  agent any

  parameters {
    string(name: 'CIVIHR_BUILDNAME', defaultValue: "civihr-dev_$BRANCH_NAME", description: 'CiviHR site name')
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
    KARMA_TESTS_REPORT_FOLDER = "reports/js-karma"
    PHPUNIT_TESTS_REPORT_FOLDER = "reports/phpunit"
  }

  stages {
    stage('Pre-tasks execution') {
      steps {
        sendBuildStartdNotification()

        // Print all Environment variables
        sh 'printenv | sort'

        // Update buildkit
        sh "cd /opt/buildkit && git pull"

        // Destroy existing site
        sh "civibuild destroy ${params.CIVIHR_BUILDNAME} || true"

        // Test build tools
        sh 'amp test'

        // Cleanup old Karma test reports
        sh "rm -f $WORKSPACE/$KARMA_TESTS_REPORT_FOLDER/* || true"

        // Cleanup old PHPUnit test reports
        sh "rm -f $WORKSPACE/$PHPUNIT_TESTS_REPORT_FOLDER/* || true"
      }
    }

    stage('Build site') {
      steps {
        script {
          // Build site with CV Buildkit
          sh "civibuild create ${params.CIVIHR_BUILDNAME} --type drupal-clean --civi-ver 4.7.27 --url $WEBURL --admin-pass $ADMIN_PASS"

          sh """
            cd $DRUPAL_MODULES_ROOT/civicrm
            wget -O attachments.patch https://gist.githubusercontent.com/davialexandre/199b3ebb2c69f43c07dde0f51fb02c8b/raw/0f11edad8049c6edddd7f865c801ecba5fa4c052/attachments-4.7.27.patch
            patch -p1 -i attachments.patch
            rm attachments.patch
          """

          // Get target and PR branches name
          def prBranch = env.CHANGE_BRANCH
          def envBranch = env.CHANGE_TARGET ? env.CHANGE_TARGET : env.BRANCH_NAME
          if (prBranch != null && prBranch.startsWith("hotfix-")) {
            envBranch = 'master'
          }

          // Clone CiviHR
          cloneCiviHRRepositories(envBranch)

          if (prBranch) {
            checkoutPrBranchInCiviHRRepos(prBranch)
            mergeEnvBranchInAllRepos(envBranch)
          }
        }
      }
    }

    stage('Run tests') {
      parallel {
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
                  ]
                ],
                tools: [
                  [
                    $class: 'JUnitType',
                    pattern: env.PHPUNIT_TESTS_REPORT_FOLDER + '/*.xml'
                  ]
                ]
              ])
            }
          }
        }

        stage('Test JS') {
          steps {
            script {
              // This is necessary to avoid an additional loop
              // in each extension folder to read the XML.
              // After each test we move the reports to this folder
              sh "mkdir -p $WORKSPACE/$KARMA_TESTS_REPORT_FOLDER"

              for (extension in listCivihrExtensions()) {
                if (extension.hasJSTests) {
                  installJSPackages(extension)
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
                  ]
                ],
                tools: [
                  [
                    $class: 'JUnitType',
                    pattern: env.KARMA_TESTS_REPORT_FOLDER + '/*.xml'
                  ]
                ]
              ])
            }
          }
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
    success {
      sendBuildSuccessNotification()
    }
    failure {
      sendBuildFailureNotification()
    }
  }
}

/*
 * Sends a notification when the build starts
 */
def sendBuildStartdNotification() {
  def message = 'Building ' + getBuildTargetLink() + '. ' + getReportLink()

  sendHipchatNotification('YELLOW', message)
}

/*
 * Sends a notification when the build is completed successfully
 */
def sendBuildSuccessNotification() {
  def message = getBuildTargetLink() + ' built successfully. Time: $BUILD_DURATION. ' + getReportLink()
  sendHipchatNotification('GREEN', message)
}

/*
 * Sends a notification when the build fails
 */
def sendBuildFailureNotification() {
  def message = 'Failed to build ' + getBuildTargetLink() + '. Time: $BUILD_DURATION. No. of failed tests: ${TEST_COUNTS,var=\"fail\"}. ' + getReportLink()
  sendHipchatNotification('RED', message)
}

/*
 * Sends a notification to Hipchat
 */
def sendHipchatNotification(String color, String message) {
  hipchatSend color: color, message: message, notify: true
}

/*
 * Returns a link to what is being built. If it's a PR, then it's a link to the pull request itself.
 * If it's a branch, then it's a link in the format http://github.com/org/repo/tree/branch
 */
def getBuildTargetLink() {
  if(buildIsForAPullRequest()) {
    return "<a href=\"${env.CHANGE_URL}\">\"${env.CHANGE_TITLE}\"</a>"
  }

  return '<a href="' + getRepositoryUrlForBuildBranch() + '">"' + env.BRANCH_NAME + '"</a>'
}

/*
 * Returns true if this build as triggered by a Pull Request.
 */
def buildIsForAPullRequest() {
  return env.CHANGE_URL != null
}

/*
 * Returns a URL pointing to branch currently being built
 */
def getRepositoryUrlForBuildBranch() {
  def repositoryURL = env.GIT_URL
  repositoryURL = repositoryURL.replace('.git', '')

  return repositoryURL + '/tree/' + env.BRANCH_NAME
}

/*
 * Returns the Blue Ocean build report URL for the current job
 */
def getReportLink() {
 return 'Click <a href="$BLUE_OCEAN_URL">here</a> to see the build report'
}

def cloneCiviHRRepositories(String envBranch) {
  for (repo in listCivihrGitRepoPath()) {
    sh """
      git clone ${repo.url} ${repo.folder}
      cd ${repo.folder}
      git checkout $envBranch || true
    """
  }
}

def checkoutPrBranchInCiviHRRepos(String branch) {
  echo 'Checking out CiviHR repos..'

  for (repo in listCivihrGitRepoPath()) {
    sh """
      cd ${repo.folder}
      git checkout ${branch} || true
    """
  }
}

def mergeEnvBranchInAllRepos(String envBranch) {
  echo 'Merging env branch'

  for (repo in listCivihrGitRepoPath()) {
    sh """
      cd ${repo.folder}
      git merge origin/${envBranch} --no-edit || true
    """
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
    phpunit4 --testsuite="Unit Tests" --log-junit $WORKSPACE/$PHPUNIT_TESTS_REPORT_FOLDER/result-phpunit_${extension
    .shortName}.xml
  """
}

/*
 * Install JS Testing
 * params: extension
 */
def installJSPackages(java.util.LinkedHashMap extension) {
  sh """
    cd $CIVICRM_EXT_ROOT/civihr/${extension.folder}
    yarn || true
  """
}

/*
 * Execute JS Testing
 * params: extension
 */
def testJS(java.util.LinkedHashMap extension) {
  echo "JS Testing ${extension.name}"

  // We cannot change, using CLI arguments, the place where
  // karma stores the junit XML report, so the last command
  // here copies the XML from the extension folder to the
  // workspace, where Jenkins will read it
  sh """
    cd $CIVICRM_EXT_ROOT/civihr/${extension.folder}
    gulp test --reporters junit,progress || true
    mv test-reports/*.xml $WORKSPACE/$KARMA_TESTS_REPORT_FOLDER/ || true
  """
}

/*
 * Get a list of CiviHR repositories
 * Note that these are NOT all the repositories used by CiviHR, but only
 * those necessary to run the Unit Tests for the CiviHR repo
 */
def listCivihrGitRepoPath() {
  return [
    [
      'url': 'https://github.com/civicrm/civihr.git',
      'folder': "$CIVICRM_EXT_ROOT/civihr"
    ],
    [
      'url': 'https://github.com/compucorp/civihr-tasks-assignments.git',
      'folder': "$CIVICRM_EXT_ROOT/civihr_tasks"
    ]
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
      hasJSTests: true,
      hasJSDependencies: false,
      hasPHPTests: true
    ],
    [
      name: 'Contacts Access Rights',
      shortName: 'contactaccessrights',
      folder: 'contactaccessrights',
      hasJSTests: true,
      hasJSDependencies: false,
      hasPHPTests: true
    ],
    [
      name: 'Contacts Summary',
      shortName: 'contactsummary',
      folder: 'contactsummary',
      hasJSTests: true,
      hasJSDependencies: false,
      hasPHPTests: false
    ],
    [
      name: 'Job Contracts',
      shortName: 'hrjobcontract',
      folder: 'hrjobcontract',
      hasJSTests: true,
      hasJSDependencies: false,
      hasPHPTests: true
    ],
    [
      name: 'Recruitment',
      shortName: 'hrrecruitment',
      folder: 'hrrecruitment',
      hasJSTests: false,
      hasJSDependencies: false,
      hasPHPTests: true
    ],
    [
      name: 'Reports',
      shortName: 'hrreport',
      folder: 'hrreport',
      hasJSTests: false,
      hasJSDependencies: false,
      hasPHPTests: false
    ],
    [
      name: 'HR UI',
      shortName: 'hrui',
      folder: 'hrui',
      hasJSTests: false,
      hasJSDependencies: false,
      hasPHPTests: false
    ],
    [
      name: 'HR Visa',
      shortName: 'hrvisa',
      folder: 'hrvisa',
      hasJSTests: false,
      hasJSDependencies: false,
      hasPHPTests: true
    ],
    [
      name: 'Reqangular',
      shortName: 'reqangular',
      folder: 'org.civicrm.reqangular',
      hasJSTests: true,
      hasJSDependencies: true,
      hasPHPTests: false
    ],
    [
      name: 'HRCore',
      shortName: 'hrcore',
      folder: 'uk.co.compucorp.civicrm.hrcore',
      hasJSTests: false,
      hasJSDependencies: false,
      hasPHPTests: true
    ],
    [
      name: 'Leave and Absences',
      shortName: 'hrleaveandabsences',
      folder: 'uk.co.compucorp.civicrm.hrleaveandabsences',
      hasJSTests: true,
      hasJSDependencies: true,
      hasPHPTests: true
    ],
    [
      name: 'Sample Data',
      shortName: 'hrsampledata',
      folder: 'uk.co.compucorp.civicrm.hrsampledata',
      hasJSTests: false,
      hasJSDependencies: false,
      hasPHPTests: true
    ],
    [
      name: 'Emergency Contacts ',
      shortName: 'hremergency',
      folder: 'org.civicrm.hremergency',
      hasJSTests: false,
      hasJSDependencies: false,
      hasPHPTests: true
    ],
    [
      name: 'Bootstrap CiviHR',
      shortName: 'bootstrapcivihr',
      folder: 'org.civicrm.bootstrapcivihr',
      hasJSTests: false,
      hasJSDependencies: true,
      hasPHPTests: false
    ],
  ]
}
