var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');
var path = require('path');

var test = require('../test');
var utils = require('../utils');

module.exports = [
  {
    name: 'test',
    fn: function (cb) {
      var sequence = utils.addExtensionCustomTasksToSequence([
        utils.spawnTaskForExtension('test:main', mainTask, utils.getCurrentExtension())
      ], 'test');

      gulpSequence.apply(null, sequence)(cb);
    }
  },
  {
    name: 'test:watch',
    fn: function (cb) {
      var extPath = utils.getExtensionPath();
      var watchPatterns = utils.addExtensionCustomWatchPatternsToDefaultList([
        path.join(extPath, '**', 'test/**/*.spec.js'),
        '!' + path.join(extPath, '**', 'test/mocks/**/*.js'),
        '!' + path.join(extPath, '**', 'test/test-main.js')
      ], 'test');

      gulp.watch(watchPatterns).on('change', function (file) {
        test.single(file.path);
      });
      cb();
    }
  }
];

/**
 * Runs all the JS unit tests of the extension
 */
function mainTask (cb) {
  test.all(cb);
}
