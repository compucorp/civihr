var detectInstalled = require('detect-installed');
var exec = require('child_process').exec;
var find = require('find');
var fs = require('fs');
var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');
var path = require('path');
var replace = require('gulp-replace');

var test = require('../test');
var utils = require('../utils');

var originalExtension;

module.exports = [
  {
    name: 'requirejs',
    fn: requireJsTask
  },
  {
    name: 'requirejs:watch',
    fn: function (cb) {
      var extPath, watchPatterns;

      if (utils.canCurrentExtensionRun('requirejs')) {
        extPath = utils.getExtensionPath();
        watchPatterns = utils.addExtensionCustomWatchPatternsToDefaultList([
          path.join(extPath, '**', 'src/**/*.js')
        ], 'requirejs');

        gulp.watch(watchPatterns, ['requirejs']).on('change', function (file) {
          if (utils.canCurrentExtensionRun('test')) {
            try {
              test.for(file.path);
            } catch (ex) {
              test.all();
            }
          }
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
 * @param  {String} buildFileContent
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
 * Takes the content of the build file on the given path,
 * and applies any required transformations on it
 *
 * @param  {String} buildFilePath
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
 * Creates a temporary build file from the default one,
 * which is then fed to the RequireJS optimizer
 *
 * @param {Function} cb
 */
function requireJsMainTask (cb) {
  var buildFilePath, tempBuildFilePath;

  if (!detectInstalled.sync('requirejs')) {
    utils.throwError('The `requirejs` package is not installed globally (http://requirejs.org/docs/optimization.html#download)');
  }

  // The original extension that the task was called with could change during
  // the execution, thus it gets saved so it can be restored later
  originalExtension = utils.getCurrentExtension();

  buildFilePath = find.fileSync('build.js', utils.getExtensionPath())[0];
  tempBuildFilePath = path.join(path.dirname(buildFilePath), 'build.tmp.js');

  fs.writeFileSync(tempBuildFilePath, processBuildFile(buildFilePath), 'utf8');

  exec('r.js -o ' + tempBuildFilePath, function (err, stdout, stderr) {
    fs.unlink(tempBuildFilePath);

    if (err && err.code) {
      console.log(stdout);
      process.exit(1);
    }

    cb();
  });
}

/**
 * Changes the sourceMappingURL inside minified files from file path to URL path.
 * This helps making source map files to be more easily located.
 *
 * @param {Function} cb
 */
function requireJsSourceMapPath (cb) {
  var distFolder;
  var extensionPath = utils.getExtensionPath();
  var extensionUrl = utils.getExtensionUrlPath();

  // finds /dist folders that are outside /node_modules:
  distFolder = find.dirSync(/^((?!node_modules).)*\/dist$/, extensionPath).pop();

  gulp.src(path.join(distFolder, '*.min.js'))
    .pipe(replace(/sourceMappingURL=(.+)/, 'sourceMappingURL=' + extensionUrl + '/$1'))
    .pipe(gulp.dest(distFolder));

  cb();
}

/**
 * Sets up and runs the task sequences
 *
 * @param {Function} cb
 */
function requireJsTask (cb) {
  var sequence;

  if (utils.canCurrentExtensionRun('requirejs')) {
    sequence = utils.addExtensionCustomTasksToSequence([
      utils.spawnTaskForExtension('requirejs:main', requireJsMainTask)
    ], 'requirejs');

    if (!utils.hasMainTaskBeenReplaced(sequence)) {
      sequence.push(
        utils.spawnTaskForExtension('requirejs:dependencies', extensionDependenciesTask),
        utils.spawnTaskForExtension('requirejs:sourceMapPath', requireJsSourceMapPath)
      );
    }

    gulpSequence.apply(null, sequence)(cb);
  } else {
    console.log('Not eligible for this task, skipping...');
    cb();
  }
}
