var gulp = require('gulp');
var gulpSequence = require('gulp-sequence')
var clean = require('gulp-clean');
var rename = require('gulp-rename');
var replace = require('gulp-replace');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var karma = require('karma');
var exec = require('child_process').exec;
var path = require('path');
var fs = require('fs');
var postcss = require('gulp-postcss');
var postcssPrefix = require('postcss-prefix-selector');
var transformSelectors = require("gulp-transform-selectors");

var bootstrapNamespace = '#bootstrap-theme';

gulp.task('requirejs', function (cb) {
  gulpSequence('requirejs:optimizer', 'requirejs:rename', 'requirejs:clean')(cb);
});

gulp.task('requirejs:clean', function () {
  return gulp.src([
    'js/angular/dist/leave-absences',
    'js/angular/dist/build.txt',
    'js/angular/dist/*.js',
    '!js/angular/dist/*.min.js'
  ], { read: false })
  .pipe(clean());
});

gulp.task('requirejs:optimizer', function (done) {
  exec('r.js -o js/angular/build.js', function (err, stdout, stderr) {
    err && err.code && console.log(stdout);
    done();
  });
});

gulp.task('requirejs:rename', function () {
  return gulp.src('js/angular/dist/*.js')
    .pipe(rename(function (path) { path.basename += '.min'; }))
    .pipe(gulp.dest('js/angular/dist'));
});

gulp.task('sass', function () {
  return gulp.src('scss/*.scss')
    .pipe(bulk())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(postcss([postcssPrefix({
      prefix: bootstrapNamespace + ' ',
      exclude: [/^html/, /^body/, /\.ta-hidden-input/]
    })]))
    .pipe(transformSelectors(namespaceRootElements, { splitOnCommas: true }))
    .pipe(gulp.dest('css/'));
});

gulp.task('watch', function () {
  gulp.watch('scss/**/*.scss', ['sass']);
  gulp.watch('js/angular/src/**/*.js', ['requirejs']).on('change', function (file) {
    try { test.for(file.path); } catch (ex) { test.all(); };
  });
  gulp.watch(['js/angular/test/**/*.js', '!js/angular/test/mocks/**/*.js', '!js/angular/test/test-main.js'])
    .on('change', function (file) {
      test.single(file.path);
  });
});

gulp.task('test', function (done) {
  test.all();
});

gulp.task('default', ['requirejs', 'sass', 'test', 'watch']);

/**
 * Apply the namespace on html and body elements
 *
 * @param  {string} selector the current selector to be transformed
 * @return string
 */
function namespaceRootElements(selector) {
  var regex = /^(body|html)/;

  if (regex.test(selector)) {
    selector = selector.replace(regex, function (match) {
      return match + bootstrapNamespace;
    }) + ",\n" + selector.replace(regex, bootstrapNamespace);
  }

  return selector;
}

var test = (function () {

  /**
   * Runs the karma server which does a single run of the test/s
   *
   * @param {string} configFile - The full path to the karma config file
   * @param {Function} cb - The callback to call when the server closes
   */
  function runServer(configFile, cb) {
    new karma.Server({
      configFile: __dirname + '/js/angular/' + configFile,
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
     * Looks for a test file (*_test.js) in `test/`, using the same path
     * of the source file in `src/leave-absences/`
     *   i.e. src/leave-absences/models/model.js -> test/models/model_test.js
     *
     * @throw {Error}
     */
    for: function (srcFile) {
      var srcFileNoExt = path.basename(srcFile, path.extname(srcFile));
      var testFile = srcFile
        .replace('src/leave-absences/', 'test/')
        .replace(srcFileNoExt + '.js', srcFileNoExt + '_test.js');

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
      var configFile = 'karma.' + path.basename(testFile, path.extname(testFile))  + '.conf.temp.js';

      gulp.src(__dirname + '/js/angular/karma.conf.js')
        .pipe(replace('*_test.js', path.basename(testFile)))
        .pipe(rename(configFile))
        .pipe(gulp.dest(__dirname + '/js/angular'))
        .on('end', function () {
          runServer(configFile, function () {
            gulp.src(__dirname + '/js/angular/' + configFile, { read: false }).pipe(clean());
          });
        });
    }
  };
})();
