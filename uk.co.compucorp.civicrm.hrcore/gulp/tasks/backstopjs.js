var _ = require('lodash');
var argv = require('yargs').argv;
var backstopjs = require('backstopjs');
var clean = require('gulp-clean');
var exec = require('child_process').exec;
var file = require('gulp-file');
var fs = require('fs');
var gulp = require('gulp');
var path = require('path');
var Promise = require('es6-promise').Promise;

var utils = require('../utils');

var BACKSTOP_DIR = 'backstop_data/';
var BACKSTOP_DIR_PATH = path.join(__dirname, '..', '..', BACKSTOP_DIR);
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

module.exports = ['reference', 'test', 'openReport', 'approve'].map(function (action) {
  return {
    name: 'backstopjs:' + action,
    fn: function () {
      return runBackstopJS(action);
    }
  };
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
 * Creates the site config file is in the backstopjs folder, if it doesn't exists yet
 *
 * @return {Boolean} Whether the file had to be created or not
 */
function touchConfigFile () {
  var created = false;

  try {
    fs.readFileSync(BACKSTOP_DIR_PATH + FILES.config);
  } catch (err) {
    fs.writeFileSync(BACKSTOP_DIR_PATH + FILES.config, JSON.stringify(CONFIG_TPL, null, 2));
    created = true;
  }

  return created;
}

/**
 * Runs backstopJS with the given command.
 *
 * It fills the template file with the list of scenarios, creates a temp
 * file passed to backstopJS, then removes the temp file once the command is completed
 *
 * @param  {String} command
 * @return {Promise}
 */
function runBackstopJS (command) {
  var destFile = 'backstop.temp.json';

  if (touchConfigFile()) {
    utils.throwError(
      'No site-config.json file detected!\n' +
      '\tOne has been created for you under ' + BACKSTOP_DIR + '\n' +
      '\tPlease insert the real value for each placeholder and try again'
    );
  }

  return getRolesAndIDs()
    .then(function (contactIdsByRoles) {
      return new Promise(function (resolve) {
        gulp.src(BACKSTOP_DIR_PATH + FILES.tpl)
          .pipe(file(destFile, tempFileContent(contactIdsByRoles)))
          .pipe(gulp.dest(BACKSTOP_DIR_PATH))
          .on('end', function () {
            var promise = backstopjs(command, {
              configPath: BACKSTOP_DIR_PATH + destFile,
              filter: argv.filter
            }).catch(_.noop).then(function () { // equivalent to .finally()
              gulp.src(BACKSTOP_DIR_PATH + destFile, { read: false }).pipe(clean());
            });

            resolve(promise);
          });
      });
    })
    .catch(function (err) {
      utils.throwError(err.message);
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
  var config = JSON.parse(fs.readFileSync(BACKSTOP_DIR_PATH + FILES.config));
  var content = JSON.parse(fs.readFileSync(BACKSTOP_DIR_PATH + FILES.tpl));

  content.scenarios = scenariosList().map(function (scenario) {
    scenario.url = constructBackstopJSScenarioUrl(config.url, scenario.url, contactIdsByRoles);

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
  var scenariosPath = BACKSTOP_DIR_PATH + 'scenarios/';

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
