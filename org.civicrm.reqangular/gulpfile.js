var gulp = require('gulp');
var exec = require('child_process').exec;
var replace = require('gulp-replace');
var templateCache = require('gulp-angular-templatecache');
var clean = require('gulp-clean');
var rename = require('gulp-rename');
var karma = require('karma');
var path = require('path');
var fs = require('fs');

gulp.task('cache-templates', function (cb) {
  gulp.src('src/common/templates/**/*.html')
    .pipe(templateCache({
      moduleSystem: 'RequireJS'
    }))
    .pipe(replace("['angular']", "['common/angular']"))
    .pipe(replace('module("templates")', 'module("common.templates", [])'))
    .pipe(gulp.dest('src/common/modules'));

  cb();
});

gulp.task('requirejs-bundle', ['cache-templates'], function (done) {
  exec('r.js -o build.js', function (err, stdout, stderr) {
    err && err.code && console.log(stdout);
    done();
  });
});

gulp.task('requirejs-bundle-mock', function (done) {
  exec('r.js -o build.mocks.js', function (err, stdout, stderr) {
    err && err.code && console.log(stdout);
    done();
  });
});

gulp.task('watch', function () {
  gulp.watch(['src/common/**/*.js', '!src/common/modules/templates.js'], ['requirejs-bundle']).on('change', function (file) {
    try { test.for(file.path); } catch (ex) { test.all(); }
  });
  gulp.watch('test/mocks/**/*.js', ['requirejs-bundle-mock']);
  gulp.watch('src/common/templates/**/*.html', ['cache-templates', 'requirejs-bundle']);
  gulp.watch(['test/**/*.js', '!test/mocks/**/*.js', '!test/test-main.js']).on('change', function (file) {
    test.single(file.path);
  });
});

gulp.task('default', ['cache-templates', 'requirejs-bundle', 'requirejs-bundle-mock', 'test']);

gulp.task('test', function (done) {
  test.all(done);
});

var test = (function () {
  /**
   * Runs the karma server which does a single run of the test/s
   *
   * @param {string} configFile - The full path to the karma config file
   * @param {Function} cb - The callback to call when the server closes
   */
  function runServer (configFile, cb) {
    new karma.Server({
      configFile: path.join(__dirname, configFile),
      singleRun: true
    }, function () {
      cb && cb();
    }).start();
  }

  return {

    /**
     * Runs all the tests
     * @param {Function} cb - The callback to call when the server closes
     */
    all: function (cb) {
      runServer('karma.conf.js', cb);
    },

    /**
     * Runs the tests for a specific source file
     *
     * Looks for a test file (*.test.js) in `test/`, using the same path
     * of the source file in `org.civicrm.reqangular/src/`
     *   i.e. common/models/model.js -> test/models/model.spec.js
     *
     * @throw {Error}
     */
    for: function (srcFile) {
      var srcFileNoExt = path.basename(srcFile, path.extname(srcFile));
      var testFile = srcFile
        .replace('src/common/', 'test/')
        .replace(srcFileNoExt + '.js', srcFileNoExt + '.spec.js');

      try {
        var stats = fs.statSync(testFile);

        stats.isFile() && this.single(testFile);
      } catch (ex) {
        throw ex;
      }
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
      var configFile = 'karma.' + path.basename(testFile, path.extname(testFile)) + '.conf.temp.js';

      gulp.src(path.join(__dirname, 'karma.conf.js'))
        .pipe(replace('*.spec.js', path.basename(testFile)))
        .pipe(rename(configFile))
        .pipe(gulp.dest(__dirname))
        .on('end', function () {
          runServer(configFile, function () {
            gulp.src(
              path.join(__dirname, configFile), { read: false }).pipe(clean());
          });
        });
    }
  };
})();
