var _ = require('lodash');
var argv = require('yargs').argv;
var backstopjs = require('backstopjs');
var clean = require('gulp-clean');
var colors = require('ansi-colors');
var file = require('gulp-file');
var fs = require('fs');
var gulp = require('gulp');
var path = require('path');
var Promise = require('es6-promise').Promise;

var backstopDir = 'backstop_data/';
var backstopDirPath = path.join(__dirname, '..', '..', backstopDir);
var files = { config: 'site-config.json', tpl: 'backstop.tpl.json' };
var configTpl = {
  'url': 'http://%{site-host}',
  'credentials': { 'name': '%{user-name}', 'pass': '%{user-password}' }
};

module.exports = [
  {
    name: 'backstopjs:reference',
    fn: function (cb) {
      runBackstopJS('reference').then(function () {
        cb();
      });
    }
  },
  {
    name: 'backstopjs:test',
    fn: function (cb) {
      runBackstopJS('test').then(function () {
        cb();
      });
    }
  },
  {
    name: 'backstopjs:report',
    fn: function (cb) {
      runBackstopJS('openReport').then(function () {
        cb();
      });
    }
  },
  {
    name: 'backstopjs:approve',
    fn: function (cb) {
      runBackstopJS('approve').then(function () {
        cb();
      });
    }
  }
];

/**
 * Checks if the site config file is in the backstopjs folder
 * If not, it creates a template for it
 *
 * @return {Boolean} [description]
 */
function isConfigFilePresent () {
  var check = true;

  try {
    fs.readFileSync(backstopDirPath + files.config);
  } catch (err) {
    fs.writeFileSync(backstopDirPath + files.config, JSON.stringify(configTpl, null, 2));
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
    console.log(colors.red(
      'No site-config.json file detected!\n' +
      'One has been created for you under ' + backstopDir + '\n' +
      'Please insert the real value for each placholder and try again'
    ));

    return Promise.reject(new Error());
  }

  return new Promise(function (resolve) {
    gulp.src(backstopDirPath + files.tpl)
      .pipe(file(destFile, tempFileContent()))
      .pipe(gulp.dest(backstopDirPath))
      .on('end', function () {
        var promise = backstopjs(command, {
          configPath: backstopDirPath + destFile,
          filter: argv.filter
        })
          .catch(_.noop).then(function () { // equivalent to .finally()
            gulp.src(backstopDirPath + destFile, { read: false }).pipe(clean());
          });

        resolve(promise);
      });
  });
}

/**
 * Creates the content of the config temporary file that will be fed to BackstopJS
 * The content is the mix of the config template and the list of scenarios
 * under the scenarios/ folder
 *
 * @return {string}
 */
function tempFileContent () {
  var config = JSON.parse(fs.readFileSync(backstopDirPath + files.config));
  var content = JSON.parse(fs.readFileSync(backstopDirPath + files.tpl));

  content.scenarios = scenariosList().map(function (scenario) {
    scenario.url = config.url + '/' + scenario.url;

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
  var scenariosPath = backstopDirPath + 'scenarios/';

  return _(fs.readdirSync(scenariosPath))
    .filter(function (scenario) {
      return argv.configFile ? scenario === argv.configFile : true;
    })
    .map(function (scenarioFile) {
      return JSON.parse(fs.readFileSync(scenariosPath + scenarioFile)).scenarios;
    })
    .flatten()
    .map(function (scenario) {
      return _.assign(scenario, { delay: scenario.delay || 6000 });
    })
    .tap(function (scenarios) {
      scenarios[0].onBeforeScript = 'login';

      return scenarios;
    })
    .value();
}
