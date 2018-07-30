var bulk = require('gulp-sass-bulk-import');
var civicrmScssRoot = require('civicrm-scssroot')();
var gulp = require('gulp');
var path = require('path');
var sass = require('gulp-sass');
var stripCssComments = require('gulp-strip-css-comments');

var utils = require('../utils');

module.exports = [
  {
    name: 'sass',
    fn: function (cb) {
      var sequence;

      if (utils.canCurrentExtensionRun('sass')) {
        sequence = utils.addExtensionCustomTasksToSequence([
          utils.spawnTaskForExtension('sass:sync', syncTask),
          utils.spawnTaskForExtension('sass:main', mainTask)
        ], 'sass');

        gulp.series.apply(null, sequence)(cb);
      } else {
        console.log('Not eligible for this task, skipping...');
        cb();
      }
    }
  },
  {
    name: 'sass:watch',
    fn: function (cb) {
      var extPath, watchPatterns;

      if (utils.canCurrentExtensionRun('sass')) {
        extPath = utils.getExtensionPath();
        watchPatterns = utils.addExtensionCustomWatchPatternsToDefaultList([
          path.join(extPath, 'scss/**/*.scss')
        ], 'sass');

        gulp.watch(watchPatterns, ['sass']);
        cb();
      } else {
        console.log('Not eligible for this task, skipping...');
        cb();
      }
    }
  }
];

/**
 * Compiles SASS files
 *
 * @param  {Function} cb
 * @return {Vinyl}
 */
function mainTask (cb) {
  var extPath = utils.getExtensionPath();

  return gulp.src(path.join(extPath, '/scss/*.scss'))
    .pipe(bulk())
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: civicrmScssRoot.getPath(),
      precision: 10
    }).on('error', function (err) {
      sass.logError.call(this, err);
      process.exit(1);
    }))
    .pipe(stripCssComments({ preserve: false }))
    .pipe(gulp.dest(path.join(extPath, 'css')));
}

/**
 * Syncs the SASS cache
 *
 * @param {Function} cb
 */
function syncTask (cb) {
  civicrmScssRoot.updateSync();
  cb();
}
