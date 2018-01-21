var colors = require('ansi-colors');
var find = require('find');
var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');
var path = require('path');

var test = require('../test');
var utils = require('../utils');

module.exports = [
  {
    name: 'test',
    fn: function (cb) {
      var sequence;

      if (hasCurrentExtensionKarmaFile()) {
        sequence = utils.addExtensionCustomTasksToSequence([
          utils.spawnTaskForExtension('test:main', mainTask)
        ], 'test');

        gulpSequence.apply(null, sequence)(cb);
      } else {
        console.log(colors.yellow('No karma.conf.js file found, skipping...'));
        cb();
      }
    }
  },
  {
    name: 'test:watch',
    fn: function (cb) {
      var extPath, watchPatterns;

      if (hasCurrentExtensionKarmaFile()) {
        extPath = utils.getExtensionPath();
        watchPatterns = utils.addExtensionCustomWatchPatternsToDefaultList([
          path.join(extPath, '**', 'test/**/*.spec.js'),
          '!' + path.join(extPath, '**', 'test/mocks/**/*.js'),
          '!' + path.join(extPath, '**', 'test/test-main.js')
        ], 'test');

        gulp.watch(watchPatterns).on('change', function (file) {
          test.single(file.path);
        });
        cb();
      } else {
        console.log(colors.yellow('No karma.conf.js file found, skipping...'));
        cb();
      }
    }
  }
];

/**
 * Check if the current extension has a karma.conf.js file
 * Allows for the file to be either in the root, or in up to two levels of folders
 *
 * @return {Boolean}
 */
function hasCurrentExtensionKarmaFile () {
  var extPath = utils.getExtensionPath();
  var karmaConfRegExp = new RegExp(extPath + '(/[^/]+)?(/[^/]+)?/karma.conf.js');

  return !!find.fileSync(karmaConfRegExp, extPath)
    // files from the node_modules/ folder might get caught up in the query
    .filter(function (filePath) {
      return !(filePath.indexOf('node_modules') > -1);
    })[0];
}

/**
 * Runs all the JS unit tests of the extension
 */
function mainTask (cb) {
  test.all(cb);
}
