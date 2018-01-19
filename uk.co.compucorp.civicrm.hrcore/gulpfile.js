var _ = require('lodash');
var argv = require('yargs').argv;
var exec = require('child_process').exec;
var gulp = require('gulp');
var clean = require('gulp-clean');
var color = require('gulp-color');
var file = require('gulp-file');
var backstopjs = require('backstopjs');
var fs = require('fs');
var Promise = require('es6-promise').Promise;

// BackstopJS tasks
(function () {
  var BACKSTOP_DIR = 'backstop_data/';
  var DEFAULT_CREDENTIAL = 'admin';
  var FILES = { config: 'site-config.json', tpl: 'backstop.tpl.json' };
  var CONFIG_TPL = {
    'url': 'http://%{site-host}',
    'credentials': {
      'admin': { 'name': '%{admin-name}', 'pass': '%{admin-password}' },
      'manager': { 'name': '%{manager-name}', 'pass': '%{manager-password}' },
      'staff': { 'name': '%{staff-name}', 'pass': '%{staff-password}' }
    }
  };

  gulp.task('backstopjs:reference', function (done) {
    runBackstopJS('reference').then(function () {
      done();
    });
  });

  gulp.task('backstopjs:test', function (done) {
    runBackstopJS('test').then(function () {
      done();
    });
  });

  gulp.task('backstopjs:report', function (done) {
    runBackstopJS('openReport').then(function () {
      done();
    });
  });

  gulp.task('backstopjs:approve', function (done) {
    runBackstopJS('approve').then(function () {
      done();
    });
  });

  /**
   * Constructs URL for BackstopJS scenario based on
   * site URL, scenario config URL and contact "roles" and IDs map
   *
   * @param  {String} siteUrl
   * @param  {String} scenarioUrl
   * @param  {Object} contactIdsByRoles
   * @return {String}
   */
  function constructBackstopJSScenarioUrl (siteUrl, scenarioUrl, contactIdsByRoles) {
    scenarioUrl = scenarioUrl.replace(/\{\{contactId:([^}]+)\}\}/g, function (fullMatch, contactRole) {
      return contactIdsByRoles[contactRole];
    });

    return siteUrl + '/' + scenarioUrl;
  }

  /**
   * Fetches civicrm contacts who's emails match "civihr_" pattern
   * and returns a map of their "roles" connected to their IDs.
   * Requires 'civihr_(staff|manager|admin)@...' to be presented in DB,
   * otherwise will throw an error.
   *
   * @return {Promise} resolved with {Object}, ex. { 'staff': 204, ... etc }
   */
  function getRolesAndIDs () {
    return new Promise(function (resolve, reject) {
      exec('cv api contact.get sequential=1 email="civihr_%" contact_type="Individual" return="email,contact_id"', function (err, result) {
        var idsByRoles, missingRoles;

        if (err) {
          return reject(new Error('Unable to fetch contact roles and IDs: ' + err));
        }

        idsByRoles = _(JSON.parse(result).values)
          .map(function (contact) {
            var role = contact.email.split('@')[0].split('_')[1];

            return [role, contact.contact_id];
          })
          .fromPairs()
          .value();

        missingRoles = _.difference(['staff', 'manager', 'admin'], _.keys(idsByRoles));

        if (missingRoles.length) {
          return reject(new Error('Required users with emails ' + missingRoles.map(function (role) {
            return 'civihr_' + role + '@*';
          }).join(', ') + ' were not found in the database'));
        }

        resolve(idsByRoles);
      });
    });
  }

  /**
   * Checks if the site config file is in the backstopjs folder
   * If not, it creates a template for it
   *
   * @return {Boolean} [description]
   */
  function isConfigFilePresent () {
    var check = true;

    try {
      fs.readFileSync(BACKSTOP_DIR + FILES.config);
    } catch (err) {
      fs.writeFileSync(BACKSTOP_DIR + FILES.config, JSON.stringify(CONFIG_TPL, null, 2));
      check = false;
    }

    return check;
  }

  /**
   * Runs backstopJS with the given command.
   *
   * It fills the template file with the list of scenarios, create a temp
   * file passed to backstopJS, then when the command is completed it removes the temp file
   *
   * @param  {string} command
   * @return {Promise}
   */
  function runBackstopJS (command) {
    var destFile = 'backstop.temp.json';

    if (!isConfigFilePresent()) {
      console.log(color(
        'No site-config.json file detected!\n' +
        'One has been created for you under ' + BACKSTOP_DIR + '\n' +
        'Please insert the real value for each placholder and try again', 'RED'
      ));

      return Promise.reject(new Error());
    }

    return getRolesAndIDs()
      .then(function (contactIdsByRoles) {
        return new Promise(function (resolve) {
          gulp.src(BACKSTOP_DIR + FILES.tpl)
            .pipe(file(destFile, tempFileContent(contactIdsByRoles)))
            .pipe(gulp.dest(BACKSTOP_DIR))
            .on('end', function () {
              var promise = backstopjs(command, {
                configPath: BACKSTOP_DIR + destFile,
                filter: argv.filter
              }).catch(_.noop).then(function () { // equivalent to .finally()
                gulp.src(BACKSTOP_DIR + destFile, { read: false }).pipe(clean());
              });

              resolve(promise);
            });
        });
      })
      .catch(function (err) {
        console.log(color(err.message, 'RED'));
      });
  }

  /**
   * Creates the content of the config temporary file that will be fed to BackstopJS
   * The content is the mix of the config template and the list of scenarios
   * under the scenarios/ folder
   *
   * @param  {Object} contactIdsByRoles
   * @return {String}
   */
  function tempFileContent (contactIdsByRoles) {
    var config = JSON.parse(fs.readFileSync(BACKSTOP_DIR + FILES.config));
    var content = JSON.parse(fs.readFileSync(BACKSTOP_DIR + FILES.tpl));

    content.scenarios = scenariosList().map(function (scenario) {
      scenario.url = constructBackstopJSScenarioUrl(config.url, scenario.url, contactIdsByRoles);
      scenario.misMatchThreshold = 0;

      return scenario;
    });

    return JSON.stringify(content);
  }

  /**
   * Concatenates all the scenarios, or returns only the scenario passed as
   * an argument to the gulp task
   *
   * The first scenario of the list gets the login script to run
   *
   * @return {Array}
   */
  function scenariosList () {
    var scenariosPath = BACKSTOP_DIR + 'scenarios/';

    return _(fs.readdirSync(scenariosPath))
      .filter(function (scenario) {
        return argv.configFile ? scenario === argv.configFile : true && scenario.endsWith('.json');
      })
      .map(function (scenarioFile) {
        return JSON.parse(fs.readFileSync(scenariosPath + scenarioFile)).scenarios;
      })
      .flatten()
      .map(function (scenario) {
        return _.assign(scenario, { delay: scenario.delay || 6000 });
      })
      .tap(function (scenarios) {
        var previousCredential;

        scenarios.forEach(function (scenario, index) {
          scenario.credential = scenario.credential || DEFAULT_CREDENTIAL;

          if (index === 0 || previousCredential !== scenario.credential) {
            scenario.onBeforeScript = 'login';

            if (index !== 0) {
              scenario.performLogout = true;
            }
          }

          previousCredential = scenario.credential;
        });

        return scenarios;
      })
      .value();
  }
})();
