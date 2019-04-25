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
    KARMA_TESTS_REPORT_FOLDER = "reports/js-karma"
    PHPUNIT_TESTS_REPORT_FOLDER = "reports/phpunit"
  }

  stages {
    stage('Pre-tasks execution') {
      steps {
        sendBuildStartNotification()

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
          sh "civibuild create ${params.CIVIHR_BUILDNAME} --type drupal-clean --civi-ver 5.3.1 --url $WEBURL"

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

          applyCoreForkPatch()

          // The JS tests use the cv tool to find the path  of an extension.
          // For it to work, the extensions have to be installed on the site
          installCiviHRExtensions()
        }
      }
    }

    stage('Run tests') {
      parallel {
        stage('Test PHP') {
          steps {
            script {
              for (item in mapToList(listCivihrExtensions())) {
                def extension = item.value

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
                    failureNewThreshold: '0',
                    failureThreshold: '0',
                    unstableNewThreshold: '0',
                    unstableThreshold: '0'
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
              installNodePackages();

              // HRCore is the extension where the JS tests are ran from
              def hrcore = listCivihrExtensions().hrcore;

              // This is necessary to avoid an additional loop
              // in each extension folder to read the XML.
              // After each test we move the reports to this folder
              sh "mkdir -p $WORKSPACE/$KARMA_TESTS_REPORT_FOLDER"

              for (item in mapToList(listCivihrExtensions())) {
                def extension = item.value

                if (extension.hasJSTests) {
                  testJS(hrcore.folder, extension)
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
                    failureNewThreshold: '0',
                    failureThreshold: '0',
                    unstableNewThreshold: '0',
                    unstableThreshold: '0'
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
def sendBuildStartNotification() {
  def msgHipChat = 'Building ' + getBuildTargetLink('hipchat') + '. ' + getReportLink('hipchat')
  def msgSlack = 'Building ' + getBuildTargetLink('slack') + '. ' + getReportLink('slack')

  sendHipchatNotification('YELLOW', msgHipChat)
  sendSlackNotification('warning', msgSlack)
}

/*
 * Sends a notification when the build is completed successfully
 */
def sendBuildSuccessNotification() {
  def msgHipChat = getBuildTargetLink('hipchat') + ' built successfully. Time: $BUILD_DURATION. ' + getReportLink('hipchat')
  def msgSlack = getBuildTargetLink('slack') + ' built successfully. Time: ' + getBuildDuration(currentBuild) + '. ' + getReportLink('slack')

  sendHipchatNotification('GREEN', msgHipChat)
  sendSlackNotification('good', msgSlack)
}

/*
 * Sends a notification when the build fails
 */
def sendBuildFailureNotification() {
  def msgHipChat = 'Failed to build ' + getBuildTargetLink('hipchat') + '. Time: $BUILD_DURATION. No. of failed tests: ${TEST_COUNTS,var=\"fail\"}. ' + getReportLink('hipchat')
  def msgSlack = 'Failed to build ' + getBuildTargetLink('slack') + '. Time: ' + getBuildDuration(currentBuild) + '. ' + getReportLink('slack')

  sendHipchatNotification('RED', msgHipChat)
  sendSlackNotification('danger', msgSlack)
}

/*
 * Sends a notification to Hipchat
 */
def sendHipchatNotification(String color, String message) {
  hipchatSend color: color, message: message, notify: true
}

/*
 * Sends a notification to Slack
 */
def sendSlackNotification(String color, String message) {
  slackSend color: color, message: message, notify: true
}

/*
 * Returns the build duration without the "and counting" suffix
 */
def getBuildDuration(build) {
  return build.durationString.replace(' and counting', '')
}

/*
 * Returns a link to what is being built. If it's a PR, then it's a link to the pull request itself.
 * If it's a branch, then it's a link in the format http://github.com/org/repo/tree/branch
 */
def getBuildTargetLink(String client) {
  def link = ''
  def forPR = buildIsForAPullRequest()

  switch (client) {
    case 'hipchat':
      link = forPR ? "<a href=\"${env.CHANGE_URL}\">\"${env.CHANGE_TITLE}\"</a>" : '<a href="' + getRepositoryUrlForBuildBranch() + '">"' + env.BRANCH_NAME + '"</a>'
      break;
    case 'slack':
      link = forPR ? "<${env.CHANGE_URL}|${env.CHANGE_TITLE}>" : '<' + getRepositoryUrlForBuildBranch() + '|' + env.BRANCH_NAME + '>'
      break;
  }

  return link
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
def getReportLink(String client) {
  def link = ''

  switch (client) {
    case 'hipchat':
      link = 'Click <a href="$BLUE_OCEAN_URL">here</a> to see the build report'
      break
    case 'slack':
      link = "Click <${env.RUN_DISPLAY_URL}|here> to see the build report"
      break
  }

  return link
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
    phpunit4 --testsuite="Unit Tests" --log-junit $WORKSPACE/$PHPUNIT_TESTS_REPORT_FOLDER/result-phpunit_${extension.folder}.xml
  """
}

/*
 * Install Node packages in all of CiviHR extensions
 */
def installNodePackages() {
  sh """
    cd $CIVICRM_EXT_ROOT/civihr/
    yarn || true
  """
}

/*
 * Execute JS Testing
 * params: hrcoreFolder
 * params: extension
 */
def testJS(hrcoreFolder, java.util.LinkedHashMap extension) {
  echo "JS Testing ${extension.name}"

  // We cannot change, using CLI arguments, the place where
  // karma stores the junit XML report, so the last command
  // here copies the XML from the extension folder to the
  // workspace, where Jenkins will read it
  sh """
    cd $CIVICRM_EXT_ROOT/civihr/${hrcoreFolder}
    npx gulp test --ext ${extension.folder} --reporters junit,progress || true

    cd $CIVICRM_EXT_ROOT/civihr/${extension.folder}
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
      'url': 'https://github.com/compucorp/civihr.git',
      'folder': "$CIVICRM_EXT_ROOT/civihr"
    ],
    [
      'url': 'https://github.com/compucorp/civihr-tasks-assignments.git',
      'folder': "$CIVICRM_EXT_ROOT/civihr_tasks"
    ],
    // These are not really dependencies for the tests, but both the shoreditch
    // and the styleguide installation is hardcoded in drush-install.sh
    // file and if the code cannot be found in the site, the installation will
    // fail
    [
      'url': 'https://github.com/compucorp/org.civicrm.shoreditch.git',
      'folder': "$CIVICRM_EXT_ROOT/org.civicrm.shoreditch"
    ],
    [
      'url': 'https://github.com/compucorp/org.civicrm.styleguide.git',
      'folder': "$CIVICRM_EXT_ROOT/org.civicrm.styleguide"
    ]
  ]
}

/*
 * Get a list of enabled CiviHR extensions
 */
def listCivihrExtensions() {
  return [
    hrjobroles: [
      name: 'Job Roles',
      folder: 'com.civicrm.hrjobroles',
      hasJSTests: true,
      hasPHPTests: true
    ],
    contactaccessrights: [
      name: 'Contacts Access Rights',
      folder: 'contactaccessrights',
      hasJSTests: true,
      hasPHPTests: true
    ],
    contactsummary: [
      name: 'Contacts Summary',
      folder: 'contactsummary',
      hasJSTests: true,
      hasPHPTests: false
    ],
    hrjobcontract: [
      name: 'Job Contracts',
      folder: 'hrjobcontract',
      hasJSTests: true,
      hasPHPTests: true
    ],
    hrreport: [
      name: 'Reports',
      folder: 'hrreport',
      hasJSTests: false,
      hasPHPTests: false
    ],
    hrui: [
      name: 'HR UI',
      folder: 'hrui',
      hasJSTests: false,
      hasPHPTests: false
    ],
    hrvisa: [
      name: 'HR Visa',
      folder: 'hrvisa',
      hasJSTests: false,
      hasPHPTests: true
    ],
    reqangular: [
      name: 'Reqangular',
      folder: 'org.civicrm.reqangular',
      hasJSTests: true,
      hasPHPTests: false
    ],
    hrcore: [
      name: 'HRCore',
      folder: 'uk.co.compucorp.civicrm.hrcore',
      hasJSTests: false,
      hasPHPTests: true
    ],
    hrleaveandabsences: [
      name: 'Leave and Absences',
      folder: 'uk.co.compucorp.civicrm.hrleaveandabsences',
      hasJSTests: true,
      hasPHPTests: true
    ],
    hrsampledata: [
      name: 'Sample Data',
      folder: 'uk.co.compucorp.civicrm.hrsampledata',
      hasJSTests: false,
      hasPHPTests: true
    ],
    hremergency: [
      name: 'Emergency Contacts ',
      folder: 'org.civicrm.hremergency',
      hasJSTests: false,
      hasPHPTests: true
    ],
    bootstrapcivihr: [
      name: 'Bootstrap CiviHR',
      folder: 'org.civicrm.bootstrapcivihr',
      hasJSTests: false,
      hasPHPTests: false
    ],
    hrcontactactionsmenu: [
      name: 'Contact Actions Menu',
      folder: 'uk.co.compucorp.civicrm.hrcontactactionsmenu',
      hasJSTests: false,
      hasJSPackages: false,
      hasPHPTests: true
    ]
  ]
}

/*
 * Converts a Hashmap to a List
 * This is mainly for supporting looping through the list of
 * extensions returned by listCivihrExtensions()
 * See this for more details:
 *  https://stackoverflow.com/questions/40159258/impossibility-to-iterate-over-a-map-using-groovy-within-jenkins-pipeline#40166064
 */
@NonCPS def mapToList(map) {
  def list = []

  for (def entry in map) {
    list.add(new java.util.AbstractMap.SimpleImmutableEntry(entry.key, entry.value))
  }

  list
}

/*
 * Installs the CiviHR extensions in the build site
 */
def installCiviHRExtensions() {
  sh """
    cd $CIVICRM_EXT_ROOT/civihr
    drush cvapi extension.refresh
    ./bin/drush-install.sh
  """
}

/**
 * Applies changes to CiviCRM from the Compucorp fork
 */
def applyCoreForkPatch() {
  sh """
    cd ${CIVICRM_EXT_ROOT}/civihr
    ./bin/apply-core-fork-patch.sh
  """
}
