var _ = require('lodash');
var argv = require('yargs').argv;
var gulp = require('gulp');
var clean = require('gulp-clean');
var file = require('gulp-file');
var backstopjs = require('backstopjs');
var fs = require('fs');
var Promise = require('es6-promise').Promise;

// BackstopJS tasks
(function () {
  var backstopDir = __dirname + '/backstop_data/';
  var tplFile = 'backstop.tpl.json';

  gulp.task('backstop:reference', function () {
    runBackstopJS('reference').then(function () {
      done();
    });
  });

  gulp.task('backstop:test', function (done) {
    runBackstopJS('test').then(function () {
      done();
    });
  });

  gulp.task('backstop:report', function () {
    backstopjs('openReport', { configPath: backstopDir + tplFile });
  });

  /**
   * Runs backstopJS with the given command.
   *
   * It fills the template file with the list of scenarios, create a temp
   * file passed to backstopJS, then when the command is completed it removes the temp file
   *
   * @param  {string} command
   * @return {Promise}
   */
  function runBackstopJS(command) {
    var destFile = 'backstop.temp.json';

    return new Promise(function (resolve) {
      gulp.src(backstopDir + tplFile)
      .pipe(file(destFile, (function () {
        var content = JSON.parse(fs.readFileSync(backstopDir + tplFile));
        content.scenarios = scenariosList();

        return JSON.stringify(content);
      }())))
      .pipe(gulp.dest(backstopDir))
      .on('end', function () {
        var promise = backstopjs(command, {
          configPath: backstopDir + destFile,
          filter: argv.filter
        })
        .catch(_.noop).then(function () { // equivalent to .finally()
          gulp.src(backstopDir + destFile, { read: false }).pipe(clean());
        });

        resolve(promise);
      });
    })
  }

  /**
   * Concatenates all the scenarios, or returns only the scenario passed as
   * an argument to the gulp task
   *
   * @return {Array}
   */
  function scenariosList() {
    var scenariosPath = backstopDir + 'scenarios/';

    return _(fs.readdirSync(scenariosPath))
      .filter(function (scenario) {
        return argv.configFile ? scenario === argv.configFile : true;
      })
      .map(function (scenarioFile) {
        return JSON.parse(fs.readFileSync(scenariosPath + scenarioFile)).scenarios;
      })
      .flatten()
      .value();
  }
})();
