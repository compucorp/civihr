var gulp = require('gulp');
var clean = require('gulp-clean');
var rename = require('gulp-rename');
var replace = require('gulp-replace');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var karma = require('karma');
var exec = require('child_process').exec;
var path = require('path');
var fs = require('fs');
var civicrmScssRoot = require('civicrm-scssroot')();
var cv = require('civicrm-cv')({ mode: 'promise' });

gulp.task('sass', ['sass-sync'], function () {
  gulp.src('scss/*.scss')
    .pipe(bulk())
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: civicrmScssRoot.getPath()
    }).on('error', sass.logError))
    .pipe(gulp.dest('css/'));
});

gulp.task('sass-sync', function () {
  civicrmScssRoot.updateSync();
});

gulp.task('requirejs-bundle', ['create-full-path-build-file'], function (done) {
  exec('r.js -o js/build.tmp.js', function (err, stdout, stderr) {
    err && err.code && console.log(stdout);
    done();
  });
});

gulp.task('create-full-path-build-file', function (done) {
  fs.readFile('./js/build.js', 'utf8', function (error, buildFile) {
    if (error) {
      throw error;
    }

    var extensions = buildFile.match(/%([A-z0-9.\-_]+)%/g) || [];
    // return unique extensions:
    extensions = extensions.filter(function (ext, index) {
      return extensions.indexOf(ext) === index;
    });

    var promises = extensions.map(function (extension) {
      var cleanExtensionName = extension.match(/%([A-z0-9.\-_]+)%/)[1];

      return cv('path -x ' + cleanExtensionName)
      .then(function (path) {
        return {
          extension: extension,
          path: path[0].value
        };
      });
    });

    Promise.all(promises).then(function (results) {
      results.forEach(function (result) {
        var extensionRegExp = new RegExp(result.extension, 'g');

        buildFile = buildFile.replace(extensionRegExp, result.path);
      });

      fs.writeFile('./js/build.tmp.js', buildFile, 'utf8', function (error) {
        if (error) {
          throw error;
        }

        done();
      });
    })
    .catch(function (error) {
      console.error(error);
      throw error;
    });
  });
});

gulp.task('watch', function () {
  gulp.watch('scss/**/*.scss', ['sass']);
  gulp.watch('js/src/**/*.js', ['requirejs-bundle']).on('change', function (file) {
    try { test.for(file.path); } catch (ex) { test.all(); }
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
  function runServer (configFile, cb) {
    new karma.Server({
      configFile: path.join(__dirname, 'js', configFile),
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
     * of the source file in `src/contactsummary/`
     *   i.e. src/contactsummary/models/model.js -> test/models/model_test.js
     *
     * @throw {Error}
     */
    for: function (srcFile) {
      var srcFileNoExt = path.basename(srcFile, path.extname(srcFile));
      var testFile = srcFile
          .replace('src/contactsummary/', 'test/')
          .replace(srcFileNoExt + '.js', srcFileNoExt + '_test.js');

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
      var configFile = 'karma.' + path.basename(testFile, path.extname(testFile)) + '.conf.temp.js';

      gulp.src(path.join(__dirname, '/js/karma.conf.js'))
        .pipe(replace('*_test.js', path.basename(testFile)))
        .pipe(rename(configFile))
        .pipe(gulp.dest(path.join(__dirname, '/js')))
        .on('end', function () {
          runServer(configFile, function () {
            gulp.src(path.join(__dirname, 'js' + configFile), { read: false }).pipe(clean());
          });
        });
    }
  };
})();
