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

var BACKSTOP_DIR = path.join(__dirname, '..', '..', 'backstop_data');
var DEFAULT_USER = 'civihr_admin';
var CONFIG_TPL = { 'url': 'http://%{site-host}' };
var FILES = {
  siteConfig: path.join(BACKSTOP_DIR, 'site-config.json'),
  temp: path.join(BACKSTOP_DIR, 'backstop.temp.json'),
  tpl: path.join(BACKSTOP_DIR, 'backstop.tpl.json')
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
 * Concatenates all the scenarios (if no specific scenario file is specified)
 *
 * @param  {Object} contactIdsByRoles
 * @return {Array}
 */
function buildScenariosList (contactIdsByRoles) {
  var config = siteConfig();
  var dirPath = path.join(BACKSTOP_DIR, 'scenarios');

  return _(fs.readdirSync(dirPath))
    .filter(function (scenario) {
      return argv.configFile ? scenario === argv.configFile : true && scenario.endsWith('.json');
    })
    .map(function (scenarioFile) {
      var scenarioPath = path.join(dirPath, scenarioFile);

      return JSON.parse(fs.readFileSync(scenarioPath)).scenarios;
    })
    .flatten()
    .map(function (scenario, index, scenarios) {
      var user = scenario.user || DEFAULT_USER;

      return _.assign(scenario, {
        cookiePath: path.join(BACKSTOP_DIR, 'cookies', user + '.json'),
        count: '(' + (index + 1) + ' of ' + scenarios.length + ')',
        url: constructScenarioUrl(config.url, scenario.url, contactIdsByRoles)
      });
    })
    .value();
}

/**
 * Removes the temp config file and sends a notification
 * based on the given outcome from BackstopJS
 *
 * @param {Boolean} success
 */
function cleanUpAndNotify (success) {
  gulp
    .src(FILES.temp, { read: false })
    .pipe(clean())
    .pipe(notify({
      message: success ? 'Success' : 'Error',
      title: 'BackstopJS',
      sound: 'Beep'
    }));
}

/**
 * Constructs URL for BackstopJS scenario based on
 * site URL, scenario config URL and contact "roles" and IDs map
 *
 * @param  {String} siteUrl
 * @param  {String} scenarioUrl
 * @param  {Object} contactIdsByRoles
 * @return {String}
 */
function constructScenarioUrl (siteUrl, scenarioUrl, contactIdsByRoles) {
  return scenarioUrl
    .replace('{{siteUrl}}', siteUrl)
    .replace(/\{\{contactId:([^}]+)\}\}/g, function (fullMatch, contactRole) {
      return contactIdsByRoles[contactRole];
    });
}

/**
 * Creates the content of the config temporary file that will be fed to BackstopJS
 * The content is the mix of the config template and the list of scenarios
 * under the scenarios/ folder
 *
 * @return {String}
 */
function createTempConfigFile () {
  var content = JSON.parse(fs.readFileSync(FILES.tpl));

  return getRolesAndIDs()
    .then(buildScenariosList)
    .then(function (scenarios) {
      content.scenarios = scenarios;

      return JSON.stringify(content);
    });
}

/**
 * Fetches civicrm contacts whose emails match "civihr_" pattern
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
  if (touchSiteConfigFile()) {
    utils.throwError(
      'No site-config.json file detected!\n' +
      '\tOne has been created for you under ' + path.basename(BACKSTOP_DIR) + '/\n' +
      '\tPlease insert the real value for each placeholder and try again'
    );
  }

  return writeCookies()
    .then(createTempConfigFile)
    .then(function (tempConfigFile) {
      return new Promise(function (resolve, reject) {
        var success = false;

        gulp.src(FILES.tpl)
          .pipe(file(path.basename(FILES.temp), tempConfigFile))
          .pipe(gulp.dest(BACKSTOP_DIR))
          .on('end', function () {
            backstopjs(command, {
              configPath: FILES.temp,
              filter: argv.filter
            })
              .then(function () {
                success = true;
              })
              .catch(_.noop).then(function () { // equivalent to .finally()
                cleanUpAndNotify(success);

                success ? resolve() : reject(new Error('BackstopJS error'));
              });
          });
      });
    })
    .catch(function (err) {
      utils.throwError(err.message);
    });
}

/**
 * Returns the content of site config file
 *
 * @return {Object}
 */
function siteConfig () {
  return JSON.parse(fs.readFileSync(FILES.siteConfig));
}

/**
 * Creates the site config file is in the backstopjs folder, if it doesn't exists yet
 *
 * @return {Boolean} Whether the file had to be created or not
 */
function touchSiteConfigFile () {
  var created = false;

  try {
    fs.readFileSync(FILES.siteConfig);
  } catch (err) {
    fs.writeFileSync(FILES.siteConfig, JSON.stringify(CONFIG_TPL, null, 2));
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
  var cookiesDir = path.join(BACKSTOP_DIR, 'cookies');
  var port = 9222;
  var config = siteConfig();
  var users = ['admin', 'civihr_admin', 'civihr_manager', 'civihr_staff'];

  if (!fs.existsSync(cookiesDir)) {
    fs.mkdirSync(cookiesDir);
  }

  return Promise.all(users.map(function (user) {
    return new Promise(function (resolve, reject) {
      var cookieFilePath = path.join(cookiesDir, user + '.json');

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
