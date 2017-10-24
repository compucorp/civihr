var gulp = require('gulp');
var clean = require('gulp-clean');
var rename = require('gulp-rename');
var replace = require('gulp-replace');
var karma = require('karma');
var exec = require('child_process').exec;
var path = require('path');
var fs = require('fs');
var argv = require('yargs').argv;
var cv = require('civicrm-cv')({ mode: 'sync' });

gulp.task('sass', function (done) {
  // The app style relies on compass's gems, so we need to rely on it
  // for the time being
  exec('compass compile', function (_, stdout, stderr) {
    console.log(stdout);
    done();
  });
});

gulp.task('requirejs', function (done) {
  buildFileManager.init().createTempFile()
    .then(runRequireJSOptimizer)
    .then(done);
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
    var reporters = argv.reporters ? argv.reporters.split(',') : ['progress'];

    new karma.Server({
      configFile: path.join(__dirname, 'js', configFile),
      reporters: reporters,
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
     * of the source file in `src/job-contract/`
     *   i.e. src/job-contract/models/model.js -> test/models/model_test.js
     *
     * @param {string} srcFile
     */
    for: function (srcFile) {
      var srcFileNoExt = path.basename(srcFile, path.extname(srcFile));
      var testFile = srcFile
        .replace('src/job-contract/', 'test/')
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

      gulp.src(path.join(__dirname, 'js/karma.conf.js'))
        .pipe(replace('*_test.js', path.basename(testFile)))
        .pipe(rename(configFile))
        .pipe(gulp.dest(path.join(__dirname, 'js')))
          .on('end', function () {
            runServer(configFile, function () {
              gulp.src(path.join(__dirname, 'js', configFile), { read: false }).pipe(clean());
            });
          });
    }
  };
})();

/**
 * The build file manager can create the temporary build file that has the
 * extensions full path and also return a list of extension paths that can
 * be watched for changes.
 *
 * @return {Object}
 */
var buildFileManager = (function () {
  var buildFileContent, requiredExtensions;
  var extensionPathRegExp = /((%{([\w._-]+)})([^'"]*))/g;

  return {
    init: init,
    createTempFile: createTempFile,
    getExtensionWatchPaths: getExtensionWatchPaths
  };

  /**
   * Creates the temporary build file and replaces the extension names with
   * their full path.
   *
   * @return {Promise} - resolves to the temporary build file.
   */
  function createTempFile () {
    var outFilePath = './js/build.tmp.js';

    requiredExtensions.forEach(function (extension) {
      buildFileContent = buildFileContent.replace(
        new RegExp(extension.placeholder, 'g'),
        extension.path
      );
    });

    return new Promise(function (resolve, reject) {
      fs.writeFile(outFilePath, buildFileContent, 'utf8', function (error) {
        if (error) {
          return reject(error);
        }

        resolve(outFilePath);
      });
    });
  }

  /**
   * Returns the extension path.
   *
   * @param {String} extensionName - the name of the extension.
   * @return {String}
   */
  function getExtensionPathByName (extensionName) {
    var cvResult = cv('path -x ' + extensionName);

    return cvResult[0].value;
  }

  /**
   * Returns watch paths for JS files that are part of the extensions specified
   * in the build file.
   *
   * @return {Array}
   */
  function getExtensionWatchPaths () {
    return requiredExtensions.map(function (extension) {
      return path.join(extension.fullPath, '/**/*.js');
    });
  }

  /**
   *
   */
  function init () {
    initBuildFileContent();
    initRequiredExtensions();

    return this;
  }

  /**
   * Stores the contents of the requirejs' build file.
   *
   * @return {String}
   */
  function initBuildFileContent () {
    buildFileContent = fs.readFileSync('./js/build.js', 'utf8');
  }

  /**
   * Initializes the list of extensions required by the build file.
   * The extensions are populated from the placeholders (${extension-name})
   * inside of the build ile content.
   *
   * @param {String} buildFileContent - A string with the contents of the build file.
   * @return {Object[]} - An array of objects, each with the following fields:
   * - placeholder: The extension placeholder. Ex: %{uk.co.compucorp.extension-name}
   * - path: The path to the extension. Ex: /root/path/to/ext/
   * - fullPath: The path + the sub path specified in the build file. Ex:
   *   /root/path/to/ext/js/angular/src/shared'
   */
  function initRequiredExtensions () {
    var name, matches, placeholder, pathToFiles, pathToExt;
    requiredExtensions = [];

    while ((matches = extensionPathRegExp.exec(buildFileContent))) {
      name = matches[3];
      placeholder = matches[2];
      pathToFiles = matches[4];
      pathToExt = getExtensionPathByName(name);

      requiredExtensions.push({
        placeholder: placeholder,
        path: pathToExt,
        fullPath: path.join(pathToExt, pathToFiles)
      });
    }
  }
})();

function runRequireJSOptimizer (buildFilePath) {
  return new Promise(function (resolve, reject) {
    exec('r.js -o ' + buildFilePath, function (err, stdout, stderr) {
      if (err && err.code) {
        console.log(stdout);
        return reject(err);
      }

      resolve();
    });
  });
}
