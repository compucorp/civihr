var _ = require('lodash');
var argv = require('yargs').argv;
var backstopjs = require('backstopjs');
var clean = require('gulp-clean');
var Chromy = require('chromy');
var exec = require('child_process').exec;
var file = require('gulp-file');
var fs = require('fs');
var gulp = require('gulp');
var notify = require('gulp-notify');
var path = require('path');
var Promise = require('es6-promise').Promise;

var utils = require('../utils');

var BACKSTOP_DIR = 'backstop_data/';
var BACKSTOP_DIR_PATH = path.join(__dirname, '..', '..', BACKSTOP_DIR);
var FILES = { siteConfig: 'site-config.json', tpl: 'backstop.tpl.json' };
var CONFIG_TPL = {
  'url': 'http://%{site-host}'
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
  return scenarioUrl
    .replace('{{siteUrl}}', siteUrl)
    .replace(/\{\{contactId:([^}]+)\}\}/g, function (fullMatch, contactRole) {
      return contactIdsByRoles[contactRole];
    });
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

  if (touchSiteConfigFile()) {
    utils.throwError(
      'No site-config.json file detected!\n' +
      '\tOne has been created for you under ' + BACKSTOP_DIR + '\n' +
      '\tPlease insert the real value for each placeholder and try again'
    );
  }

  return writeCookies()
    .then(getRolesAndIDs)
    .then(function (contactIdsByRoles) {
      return new Promise(function (resolve, reject) {
        var isBackstopJSSuccessful;

        gulp.src(BACKSTOP_DIR_PATH + FILES.tpl)
          .pipe(file(destFile, tempFileContent(contactIdsByRoles)))
          .pipe(gulp.dest(BACKSTOP_DIR_PATH))
          .on('end', function () {
            backstopjs(command, {
              configPath: BACKSTOP_DIR_PATH + destFile,
              filter: argv.filter
            })
              .then(function () {
                isBackstopJSSuccessful = true;
              })
              .catch(_.noop).then(function () { // equivalent to .finally()
                return gulp
                  .src(BACKSTOP_DIR_PATH + destFile, { read: false })
                  .pipe(clean())
                  .pipe(notify({
                    message: isBackstopJSSuccessful ? 'Successful' : 'Error',
                    title: 'BackstopJS',
                    sound: 'Beep'
                  }));
              })
              .then(function () {
                isBackstopJSSuccessful ? resolve() : reject(new Error('BackstopJS error'));
              });
          });
      });
    })
    .catch(function (err) {
      utils.throwError(err.message);
    });
}

/**
 * Concatenates all the scenarios (if no specific scenario file is specified)
 *
 * @param  {Object} contactIdsByRoles
 * @return {Array}
 */
function scenariosList (contactIdsByRoles) {
  var config = siteConfig();
  var scenariosPath = BACKSTOP_DIR_PATH + 'scenarios/';

  return _(fs.readdirSync(scenariosPath))
    .filter(function (scenario) {
      return argv.configFile ? scenario === argv.configFile : true && scenario.endsWith('.json');
    })
    .map(function (scenarioFile) {
      return JSON.parse(fs.readFileSync(scenariosPath + scenarioFile)).scenarios;
    })
    .flatten()
    .map(function (scenario, index, scenarios) {
      return _.assign(scenario, {
        cookiePath: path.join(BACKSTOP_DIR, 'cookies', (scenario.user || 'admin') + '.json'),
        count: '(' + (index + 1) + ' of ' + scenarios.length + ')',
        delay: scenario.delay || 6000,
        url: constructBackstopJSScenarioUrl(config.url, scenario.url, contactIdsByRoles)
      });
    })
    .value();
}

/**
 * Returns the content of site config file
 *
 * @return {Object}
 */
function siteConfig () {
  return JSON.parse(fs.readFileSync(BACKSTOP_DIR_PATH + FILES.siteConfig));
}

/**
 * Creates the content of the config temporary file that will be fed to BackstopJS
 * The content is the mix of the config template and the list of scenarios
 * under the scenarios/ folder
 *
 * @return {String}
 */
function tempFileContent (contactIdsByRoles) {
  var content = JSON.parse(fs.readFileSync(BACKSTOP_DIR_PATH + FILES.tpl));

  content.scenarios = scenariosList(contactIdsByRoles);

  return JSON.stringify(content);
}

/**
 * Creates the site config file is in the backstopjs folder, if it doesn't exists yet
 *
 * @return {Boolean} Whether the file had to be created or not
 */
function touchSiteConfigFile () {
  var created = false;

  try {
    fs.readFileSync(BACKSTOP_DIR_PATH + FILES.siteConfig);
  } catch (err) {
    fs.writeFileSync(BACKSTOP_DIR_PATH + FILES.siteConfig, JSON.stringify(CONFIG_TPL, null, 2));
    created = true;
  }

  return created;
}

/**
 * Writes the session cookie files that will be used to log in as different users
 *
 * It uses the [`drush uli`](https://drushcommands.com/drush-7x/user/user-login/)
 * command to generate a one-time login url, the browser then go to that url
 * which then creates the session cookie
 *
 * The cookie is then stored in a json file which is used by the BackstopJS scenarios
 * to log in
 *
 * @return {Promise}
 */
function writeCookies () {
  var port = 9222;
  var config = siteConfig();
  var users = ['admin', 'civihr_admin', 'civihr_manager', 'civihr_staff'];

  return Promise.all(users.map(function (user) {
    return new Promise(function (resolve, reject) {
      var cookieFilePath = path.join(BACKSTOP_DIR_PATH, 'cookies', user + '.json');

      if (fs.existsSync(cookieFilePath)) {
        fs.unlinkSync(cookieFilePath);
      }

      exec('drush uli --name=' + user + ' --uri=' + config.url + ' --browser=0', function (err, loginUrl) {
        var chromy;

        if (err) {
          return reject(new Error(err));
        }

        chromy = new Chromy({ port: port++ });
        chromy.chain()
          .goto(config.url)
          .goto(loginUrl)
          .getCookies()
          .result(function (cookies) {
            fs.writeFileSync(cookieFilePath, JSON.stringify(cookies));
          })
          .end()
          .then(function () {
            chromy.close();
            resolve();
          });
      });
    });
  }));
}
