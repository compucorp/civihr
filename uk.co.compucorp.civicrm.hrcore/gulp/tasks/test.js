var gulp = require('gulp');
var path = require('path');
var test = require('../test');
var utils = require('../utils');

module.exports = [
  {
    name: 'test',
    fn: function (cb) {
      var sequence;

      if (utils.canCurrentExtensionRun('test')) {
        sequence = utils.addExtensionCustomTasksToSequence([
          utils.spawnTaskForExtension('test:main', mainTask)
        ], 'test');

        gulp.series.apply(null, sequence)(cb);
      } else {
        console.log('Not eligible for this task, skipping...');
        cb();
      }
    }
  },
  {
    name: 'test:watch',
    fn: function (cb) {
      var testFolderPath, watchPatterns;

      if (utils.canCurrentExtensionRun('test')) {
        testFolderPath = path.join(utils.getExtensionPath(), 'js/test');
        watchPatterns = utils.addExtensionCustomWatchPatternsToDefaultList([
          path.join(testFolderPath, '**/*.spec.js'),
          '!' + path.join(testFolderPath, 'test/mocks/**/*.js'),
          '!' + path.join(testFolderPath, 'test/test-main.js')
        ], 'test');

        gulp.watch(watchPatterns).on('change', function (file) {
          test.single(file.path);
        });
        cb();
      } else {
        console.log('Not eligible for this task, skipping...');
        cb();
      }
    }
  }
];

/**
 * Runs all the JS unit tests of the extension
 */
function mainTask (cb) {
  test.all(cb);
}
