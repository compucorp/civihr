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

					echo "Build branch: ${buildBranch}"

					// Build site with CV Buildkit
					echo 'Build site with CV Buildkit'
					sh "civibuild create ${params.CVHR_BUILDNAME} --type hr16 --civi-ver 4.7.18 --hr-ver ${buildBranch} --url $WEBURL --admin-pass $ADMIN_PASS"

					// Change git remote of civihr ext to support dev version of Jenkins pipeline
					changeCivihrGitRemote()

					// Get repos & branch name
					def prBranch = env.CHANGE_BRANCH
					def envBranch = env.CHANGE_TARGET
					if (prBranch != null && prBranch.startsWith("hotfix-")) {
						envBranch = 'master'
					}

					// DEBUG
					// echo "envBranch: ${envBranch} prBranch: ${prBranch}"

					// Checkout PR Branch in CiviHR repos
					echo 'Checking out CiviHR repos..'
					sh """
						cd $CVCRM_EXT_ROOT
						git-scan foreach -c \"git checkout -b testing-${prBranch} --track remotes/origin/${prBranch}\" || true
					"""

					// Merge PR Branch in CiviHR repos
					def cvhrRepos = listCivihrGitRepoPath()
					for (int i=0; i<cvhrRepos.size(); i++) {
						tokens = cvhrRepos[i].tokenize('/');
						echo 'Merging ' + tokens[tokens.size()-1]
						try {
							sh """
								cd ${cvhrRepos[i]}
								git merge origin/${envBranch} --no-edit
							"""
						} catch (err) {
							echo "Something failed at Check out PR Branch in CiviHR extension: ${cvhrRepos[i]}"
							echo "Failed: ${err}"
						}
					}

					// Upgrade Drupal & CiviCRM extensions
					echo 'Upgrade Drupal & CV extensions'
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
					// Get civihr extensions list
					def extensions = listCivihrExtensions()

					// Execute PHP test
					for (int i = 0; i<extensions.size(); i++) {
						testPHPUnit(extensions[i])
					}
				}
			}
			post {
                always {
                	// XUnit
					step([
	                    $class: 'XUnitBuilder',
                    	thresholds: [
	                    	[$class: 'FailedThreshold',
	                          failureNewThreshold: '5',
	                          failureThreshold: '5',
	                          unstableNewThreshold: '1',
	                          unstableThreshold: '1'],
	                        [$class: 'SkippedThreshold',
	                          failureNewThreshold: '0',
	                          failureThreshold: '0',
	                          unstableNewThreshold: '0',
	                          unstableThreshold: '0']
                    	],
	                    tools: [[$class: 'JUnitType', pattern: 'reports/phpunit/*.xml']]
	                ])
            	}
            }
	    }

		/* Testing JS */
		// TODO: Execute test and Generate report without stop on fail
	    stage('Testing JS: Install NPM in parallel') {
			steps {
				script {
					// Get civihr extensions list
					def extensions = listCivihrExtensions()
					def extensionTestings = [:]

					// Install NPM jobs
					for (int i = 0; i<extensions.size(); i++) {
						def index = i
						extensionTestings[extensions[index]] = {
						  echo 'Installing NPM: ' + extensions[index]
						  installNPM(extensions[index])
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
					// Get civihr extensions list
					def extensions = listCivihrExtensions()
					def extensionTestings = [:]

					// Testing JS in sequent
					for (int j = 0; j<extensions.size(); j++) {
						def index = j
						echo 'Testing with Gulp: ' + extensions[index]
						testJS(extensions[index])
					}
				}
			}
			post {
                always {
                	// XUnit
					step([
	                    $class: 'XUnitBuilder',
                    	thresholds: [
	                    	[$class: 'FailedThreshold',
	                          failureNewThreshold: '5',
	                          failureThreshold: '5',
	                          unstableNewThreshold: '1',
	                          unstableThreshold: '1'],
	                        [$class: 'SkippedThreshold',
	                          failureNewThreshold: '0',
	                          failureThreshold: '0',
	                          unstableNewThreshold: '0',
	                          unstableThreshold: '0']
                    	],
	                    tools: [[$class: 'JUnitType', pattern: 'reports/js-karma/*.xml']]
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
 *	Change URL Git remote of civihr main repositry to the URL where configured by Jenkins project
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
/*
 * Execute PHPUnit testing
 * params: extensionName
 */
def testPHPUnit(String extensionName){
	def extensionShortName = extensionName.tokenize('.')[-1]

	echo "PHPUnit testing: ${extensionShortName}"

	sh """
		cd $CVCRM_EXT_ROOT/civihr/${extensionName}
		phpunit4 \
			--log-junit $WORKSPACE/reports/phpunit/result-phpunit_${extensionShortName}.xml \
			--coverage-html $WORKSPACE/reports/phpunit/resultPHPUnitHtml_${extensionShortName} \
			|| true
	"""
}
/*
 * Installk JS Testing
 * params: extensionName
 */
def installNPM(String extensionName){
	sh """
		cd $CVCRM_EXT_ROOT/civihr/${extensionName}
		npm install || true
	"""
}
/*
 * Execute JS Testing
 * params: extensionName
 */
def testJS(String extensionName){
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
def listCivihrExtensions(){
	// All enabled cvhr extensions
	// return sh(returnStdout: true, script: "cd /opt/buildkit/build/hr17/sites/; drush cvapi extension.get statusLabel=Enabled return=path | grep '/civihr/' | awk -F '[//]' '{print \$NF}' | sort").split("\n")

 	// Manually select cvhr extensions
 	return [
 		'uk.co.compucorp.civicrm.hrcore',
 		'uk.co.compucorp.civicrm.hrleaveandabsences',
 		'com.civicrm.hrjobroles',
 	]
}

