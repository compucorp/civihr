var detectInstalled = require('detect-installed');
var exec = require('child_process').exec;
var find = require('find');
var fs = require('fs');
var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');
var path = require('path');

var test = require('../test');
var utils = require('../utils');

var originalExtension;

module.exports = [
  {
    name: 'requirejs',
    fn: function (cb) {
      // The original extension that the task was called with could change during
      // the execution, thus it gets saved so it can be restored later
      originalExtension = utils.getCurrentExtension();

      requireJsTask(cb);
    }
  },
  {
    name: 'requirejs:watch',
    fn: function (cb) {
      var extPath = utils.getExtensionPath();
      var watchPatterns = utils.addExtensionCustomWatchPatternsToDefaultList([
        path.join(extPath, '**', 'src/**/*.js')
      ], 'requirejs');

      gulp.watch(watchPatterns, ['requirejs']).on('change', function (file) {
        try {
          test.for(file.path);
        } catch (ex) {
          test.all();
        }
      });
      cb();
    }
  }
];

/**
 * It scans the build.js file of all the CiviHR extension, to check if in any of them
 * the current extension is marked as a dependency.
 *
 * If any are found, then the `requirejs` task is executed for the extension
 * the build.js file belongs to.
 *
 * The task is recursive, stopping when no further dependencies are found
 *
 * @param {Function} cb
 */
function extensionDependenciesTask (cb) {
  var buildFiles = find.fileSync(/js(\/[^/]+)?\/build\.js$/, path.join(__dirname, '../../../'));

  var sequence = buildFiles.filter(function (buildFile) {
    var content = fs.readFileSync(buildFile, 'utf8');

    return (new RegExp(utils.getCurrentExtension(), 'g')).test(content);
  })
    .map(function (buildFileWithDependency) {
      var extension = utils.getExtensionNameFromFile(buildFileWithDependency);

      return utils.spawnTaskForExtension('requirejs', requireJsTask, extension);
    });

  sequence.length ? gulpSequence.apply(null, sequence)(function () {
    // Restore the original extension (used in the CLI) as the current extension
    // before marking the task as done
    utils.setCurrentExtension(originalExtension);

    cb();
  }) : cb();
}

/**
 * Goes through the given build file content and, for each
 * extension's placeholder (%{extension-name}) found, it creates an object
 * with said placholder and the extension's local path
 *
 * @param {String} buildFileContent
 * @return {Array}
 */
function getDependencyExtensionsData (buildFileContent) {
  var matches;
  var placeholderRegExp = /(%{([^}]+)})/g;
  var requiredExtensions = [];

  while ((matches = placeholderRegExp.exec(buildFileContent)) !== null) {
    requiredExtensions.push({
      placeholder: matches[1],
      path: utils.getExtensionPath(matches[2])
    });
  }

  return requiredExtensions;
}

/**
 * Takes the content of the build file on the given path, and applies any
 * required transformations on it
 *
 * @param {String} buildFilePath
 * @return {String}
 */
function processBuildFile (buildFilePath) {
  var buildFileContent = fs.readFileSync(buildFilePath, 'utf8');

  getDependencyExtensionsData(buildFileContent).forEach(function (extension) {
    buildFileContent = buildFileContent.replace(
      new RegExp(extension.placeholder, 'g'),
      extension.path
    );
  });

  return buildFileContent;
}

/**
 * Creates a temporary build file from the default one, which is then
 * fed to the RequireJS optimizer
 *
 * @param {Function} cb
 */
function requireJsMainTask (cb) {
  var buildFilePath = find.fileSync('build.js', utils.getExtensionPath())[0];
  var tempBuildFilePath = path.join(path.dirname(buildFilePath), 'build.tmp.js');

  fs.writeFileSync(tempBuildFilePath, processBuildFile(buildFilePath), 'utf8');

  exec('r.js -o ' + tempBuildFilePath, function (err, stdout, stderr) {
    err && err.code && console.log(stdout);

    fs.unlink(tempBuildFilePath);
    cb();
  });
}

/**
 * Sets up and runs the task sequences, adding the dependencies task at the end
 *
 * @param {Function} cb
 */
function requireJsTask (cb) {
  var sequence;

  if (!detectInstalled.sync('requirejs')) {
    utils.throwError('requirejs', 'The `requirejs` package is not installed globally (http://requirejs.org/docs/optimization.html#download)');
  }

  sequence = utils.addExtensionCustomTasksToSequence([
    utils.spawnTaskForExtension('requirejs:main', requireJsMainTask, utils.getCurrentExtension())
  ], 'requirejs');
  sequence.push(utils.spawnTaskForExtension('requirejs:dependencies', extensionDependenciesTask, utils.getCurrentExtension()));

  gulpSequence.apply(null, sequence)(cb);
}
