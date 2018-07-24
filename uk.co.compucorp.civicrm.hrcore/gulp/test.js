var clean = require('gulp-clean');
var fs = require('fs');
var gulp = require('gulp');
var karma = require('karma');
var path = require('path');
var rename = require('gulp-rename');
var replace = require('gulp-replace');

var utils = require('./utils');

module.exports = {
  all: all,
  for: forFile,
  single: single
};

/**
 * Runs all the test of an extension, using the karma.conf.js file as is
 */
function all (cb) {
  var configFile = path.join(utils.getExtensionPath(), 'js/karma.conf.js');

  runServer(configFile, cb);
}

/**
 * Runs the test of the given source file
 *
 * @param {String} srcFile
 */
function forFile (srcFile) {
  var srcFileNoExt = path.basename(srcFile, path.extname(srcFile));

  var testFile = srcFile
    .replace(/src\/[^/]+\//, 'test/')
    .replace(srcFileNoExt + '.js', srcFileNoExt + '.spec.js');

  fs.statSync(testFile).isFile() && this.single(testFile);
}

/**
 * Runs the karma server which does a single run of the test(s)
 *
 * @param {string} configFile - The full path to the karma config file
 * @param {Function} cb - The callback to call when the server closes
 */
function runServer (configFile, cb) {
  new karma.Server({
    configFile: configFile,
    singleRun: true
  }, function () {
    cb && cb();
  }).start();
}

/**
 * Runs the tests suite of the given test file
 *
 * @param {String} testFile
 */
function single (testFile) {
  var jsFolderPath = path.join(utils.getExtensionPath(), 'js');
  var configFilePath = path.join(jsFolderPath, 'karma.conf.js');
  var tempConfigFile = 'karma.' + path.basename(testFile, path.extname(testFile)) + '.conf.temp.js';

  gulp
    .src(configFilePath)
    .pipe(replace('*.spec.js', path.basename(testFile)))
    .pipe(rename(tempConfigFile))
    .pipe(gulp.dest(jsFolderPath))
    .on('end', function () {
      runServer(path.join(jsFolderPath, tempConfigFile), function () {
        gulp
          .src(path.join(jsFolderPath, tempConfigFile), { read: false })
          .pipe(clean({ force: true }));
      });
    });
}
