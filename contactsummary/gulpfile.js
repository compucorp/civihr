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
var cv = require('civicrm-cv')({ mode: 'sync' });

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

gulp.task('requirejs', function (done) {
  buildFileManager().createTempFile();

  exec('r.js -o js/build.tmp.js', function (err, stdout, stderr) {
    err && err.code && console.log(stdout);
    done();
  });
});

gulp.task('watch', function () {
  var sourcePath = 'js/src/**/*.js';
  var watchPaths = buildFileManager().getWatchPaths();
  watchPaths.push(sourcePath);

  gulp.watch('scss/**/*.scss', ['sass']);
  gulp.watch(watchPaths, ['requirejs']).on('change', function (file) {
    try { test.for(file.path); } catch (ex) { test.all(); }
  });
  gulp.watch(['js/test/**/*.js', '!js/test/mocks/**/*.js', '!js/test/test-main.js']).on('change', function (file) {
    test.single(file.path);
  });
});

gulp.task('test', function (done) {
  test.all();
});

gulp.task('default', ['requirejs', 'sass', 'test', 'watch']);

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

/**
 * Returns an object that can interact with the build.js file. The object can
 * create a temporary build file using the full extension paths, or returns
 * a list of directories to watch for JS changes.
 *
 * @return {Object}
 */
function buildFileManager () {
  var buildFileContent, requiredExtensions;

  /**
   * Initializes the Build File Manager by extracting the required extensions
   * and finding the full path for each extension.
   */
  (function init () {
    initRequiredExtensions();
    initAliasFullPath();
  })();

  /**
   * Creates the temporary build file and replaces the extension names with
   * their full path.
   */
  function createTempFile () {
    var tempBuildFileContent = buildFileContent;

    requiredExtensions.forEach(function (record) {
      var extensionRegExp = new RegExp(record.alias.path, 'g');

      tempBuildFileContent = tempBuildFileContent.replace(
        extensionRegExp, record.alias.fullPath);
    });

    fs.writeFileSync('./js/build.tmp.js', tempBuildFileContent, 'utf8');
  }

  /**
   * Returns the alias name and path.
   *
   * @param {Array} parts - an array containing the raw name and path for
   * the alias.
   * @return {Object}
   */
  function getAliasObject (parts) {
    return {
      name: getTrimmedWords(parts[0]),
      path: parts[1].trim().replace(/['"]+/g, '')
    };
  }

  /**
   * Returns the extension name and path. The path is retrieved using the
   * civicrm-cv tool, which returns the full path for an extension.
   *
   * @param {Array} parts - an array containing the raw name for the extension.
   * @return {Object}
   */
  function getExtensionObject (parts) {
    var name = getTrimmedWords(parts[1]);
    var path = cv('path -x ' + name);

    return {
      name: name,
      path: path[0].value
    };
  }

  /**
   * Returns a clean string by removing unwanted characters such as '', "", %%
   * and :, and removing extra white space.
   *
   * @param {String} string - the string to clean.
   * @param {String}
   */
  function getTrimmedWords (string) {
    var words = /([\w ._-]+)/;

    return words.exec(string.trim())[0];
  }

  /**
   * Return watch paths for JS files that are part of the extensions specified
   * in the build file.
   *
   * @return {Array}
   */
  function getWatchPaths () {
    return requiredExtensions.map(function (record) {
      return record.alias.fullPath + '/**/**.js';
    });
  }

  /**
   * Initializes each alias full path by replacing the extension name, with
   * the extension path.
   */
  function initAliasFullPath () {
    requiredExtensions.forEach(function (record) {
      var extRegExp = new RegExp('%' + record.extension.name + '%');

      record.alias.fullPath = record.alias.path.replace(
        extRegExp, record.extension.path);
    });
  }

  /**
   * Initializes the required extensions by extracting them from the build file.
   * The required extensions is an array detailing the name and path for both
   * the alias (ex: leave-absences) and the extension
   * (ex: uk.co.compucorp.civicrm.hrleaveandabsences).
   */
  function initRequiredExtensions () {
    var paths;
    buildFileContent = fs.readFileSync('./js/build.js', 'utf8');
    paths = buildFileContent.match(/.*%([\w._-]+)%.*/g) || [];

    requiredExtensions = paths.map(function (path) {
      var parts = path.split(':');

      return {
        alias: getAliasObject(parts),
        extension: getExtensionObject(parts)
      };
    });
  }

  return {
    createTempFile: createTempFile,
    getWatchPaths: getWatchPaths
  };
}
