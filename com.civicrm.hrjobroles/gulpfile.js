var exec = require('child_process').exec;
var civicrmScssRoot = require('civicrm-scssroot')();
var fs = require('fs');
var gulp = require('gulp');
var clean = require('gulp-clean');
var rename = require('gulp-rename');
var replace = require('gulp-replace');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var karma = require('karma');
var path = require('path');

gulp.task('sass', ['sass:sync'], function () {
  return gulp.src('scss/*.scss')
    .pipe(bulk())
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: civicrmScssRoot.getPath(),
      precision: 10
    }).on('error', sass.logError))
    .pipe(gulp.dest('css/'));
});

gulp.task('sass:sync', function(){
  civicrmScssRoot.updateSync();
});

gulp.task('requirejs-bundle', function (done) {
  exec('r.js -o js/build.js', function (err, stdout, stderr) {
    err && err.code && console.log(stdout);
    done();
  });
});

gulp.task('watch', function () {
  gulp.watch('scss/**/*.scss', ['sass']);
  gulp.watch('js/src/**/*.js', ['requirejs-bundle']).on('change', function (file) {
    try { test.for(file.path); } catch (ex) { test.all(); };
  });
  gulp.watch(['js/test/**/*.js', '!js/test/mocks/**/*.js', '!js/test/test-main.js']).on('change', function (file) {
    test.single(file.path);
  });
});

gulp.task('test', function (done) {
  test.all();
});

gulp.task('default', ['requirejs-bundle', 'sass', 'test', 'watch']);



var test = (function () {

  /**
   * Runs the karma server which does a single run of the test/s
   *
   * @param {string} configFile - The full path to the karma config file
   * @param {Function} cb - The callback to call when the server closes
   */
  function runServer(configFile, cb) {
    new karma.Server({
      configFile: __dirname + '/js/' + configFile,
      singleRun: true
    }, function () {
      cb && cb();
    }).start();
  }

  return {

    /**
     * Runs all the tests
     */
    all: function () {
      runServer('karma.conf.js');
    },

    /**
     * Runs the tests for a specific source file
     *
     * Looks for a test file (*.spec.js) in `test/`, using the same path
     * of the source file in `src/job-roles/`
     *   i.e. src/job-roles/models/model.js -> test/models/model.spec.js
     *
     * @param {string} srcFile
     */
    for: function (srcFile) {
      var srcFileNoExt = path.basename(srcFile, path.extname(srcFile));
      var testFile = srcFile
        .replace('src/job-roles/', 'test/')
        .replace(srcFileNoExt + '.js', srcFileNoExt + '.spec.js');

      fs.statSync(testFile).isFile() && this.single(testFile);
    },

    /**
     * Runs a single test file
     *
     * It passes to the karma server a temporary config file
     * which is deleted once the test has been run
     *
     * @param {string} testFile - The full path of a test file
     */
    single: function (testFile) {
      var configFile = 'karma.' + path.basename(testFile, path.extname(testFile))  + '.conf.temp.js';

      gulp
        .src(__dirname + '/js/karma.conf.js')
        .pipe(replace('*.spec.js', path.basename(testFile)))
        .pipe(rename(configFile))
        .pipe(gulp.dest(__dirname + '/js'))
        .on('end', function () {
          runServer(configFile, function () {
            gulp.src(__dirname + '/js/' + configFile, { read: false }).pipe(clean());
          });
        });
    }
  };
})();
