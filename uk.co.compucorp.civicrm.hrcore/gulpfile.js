var _ = require('lodash');
var argv = require('yargs').argv;
var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');
var clean = require('gulp-clean');
var colors = require('ansi-colors');
var file = require('gulp-file');
var backstopjs = require('backstopjs');
var fs = require('fs');
var path = require('path');
var PluginError = require('plugin-error');
var Promise = require('es6-promise').Promise;
var cv = require('civicrm-cv')({ mode: 'sync' });
var find = require('find');
var findUp = require('find-up');
var xml = require('xml-parse');

var currentExtension;

// BackstopJS tasks
(function () {
  var backstopDir = 'backstop_data/';
  var files = { config: 'site-config.json', tpl: 'backstop.tpl.json' };
  var configTpl = {
    'url': 'http://%{site-host}',
    'credentials': { 'name': '%{user-name}', 'pass': '%{user-password}' }
  };

  gulp.task('backstopjs:reference', function (done) {
    runBackstopJS('reference').then(function () {
      done();
    });
  });

  gulp.task('backstopjs:test', function (done) {
    runBackstopJS('test').then(function () {
      done();
    });
  });

  gulp.task('backstopjs:report', function (done) {
    runBackstopJS('openReport').then(function () {
      done();
    });
  });

  gulp.task('backstopjs:approve', function (done) {
    runBackstopJS('approve').then(function () {
      done();
    });
  });

  /**
   * Checks if the site config file is in the backstopjs folder
   * If not, it creates a template for it
   *
   * @return {Boolean} [description]
   */
  function isConfigFilePresent () {
    var check = true;

    try {
      fs.readFileSync(backstopDir + files.config);
    } catch (err) {
      fs.writeFileSync(backstopDir + files.config, JSON.stringify(configTpl, null, 2));
      check = false;
    }

    return check;
  }

  /**
   * Runs backstopJS with the given command.
   *
   * It fills the template file with the list of scenarios, create a temp
   * file passed to backstopJS, then when the command is completed it removes the temp file
   *
   * @param  {string} command
   * @return {Promise}
   */
  function runBackstopJS (command) {
    var destFile = 'backstop.temp.json';

    if (!isConfigFilePresent()) {
      console.log(colors.red(
        'No site-config.json file detected!\n' +
        'One has been created for you under ' + backstopDir + '\n' +
        'Please insert the real value for each placholder and try again'
      ));

      return Promise.reject(new Error());
    }

    return new Promise(function (resolve) {
      gulp.src(backstopDir + files.tpl)
        .pipe(file(destFile, tempFileContent()))
        .pipe(gulp.dest(backstopDir))
        .on('end', function () {
          var promise = backstopjs(command, {
            configPath: backstopDir + destFile,
            filter: argv.filter
          })
            .catch(_.noop).then(function () { // equivalent to .finally()
              gulp.src(backstopDir + destFile, { read: false }).pipe(clean());
            });

          resolve(promise);
        });
    });
  }

  /**
   * Creates the content of the config temporary file that will be fed to BackstopJS
   * The content is the mix of the config template and the list of scenarios
   * under the scenarios/ folder
   *
   * @return {string}
   */
  function tempFileContent () {
    var config = JSON.parse(fs.readFileSync(backstopDir + files.config));
    var content = JSON.parse(fs.readFileSync(backstopDir + files.tpl));

    content.scenarios = scenariosList().map(function (scenario) {
      scenario.url = config.url + '/' + scenario.url;

      return scenario;
    });

    return JSON.stringify(content);
  }

  /**
   * Concatenates all the scenarios, or returns only the scenario passed as
   * an argument to the gulp task
   *
   * The first scenario of the list gets the login script to run
   *
   * @return {Array}
   */
  function scenariosList () {
    var scenariosPath = backstopDir + 'scenarios/';

    return _(fs.readdirSync(scenariosPath))
      .filter(function (scenario) {
        return argv.configFile ? scenario === argv.configFile : true;
      })
      .map(function (scenarioFile) {
        return JSON.parse(fs.readFileSync(scenariosPath + scenarioFile)).scenarios;
      })
      .flatten()
      .map(function (scenario) {
        return _.assign(scenario, { delay: scenario.delay || 6000 });
      })
      .tap(function (scenarios) {
        scenarios[0].onBeforeScript = 'login';

        return scenarios;
      })
      .value();
  }
})();

// Sass
(function () {
  var bulk = require('gulp-sass-bulk-import');
  var civicrmScssRoot = require('civicrm-scssroot')();
  var sass = require('gulp-sass');
  var stripCssComments = require('gulp-strip-css-comments');

  gulp.task('sass', function (cb) {
    if (hasCurrentExtensionMainSassFile()) {
      var sequence = addExtensionCustomTasksToSequence(['sass:main'], 'sass');

      gulpSequence.apply(null, sequence)(cb);
    } else {
      console.log(colors.yellow('No main .scss file found, skipping...'));
      cb();
    }
  });

  gulp.task('sass:main', ['sass:sync'], function (cb) {
    var extPath = getExtensionPath();

    return gulp.src(path.join(extPath, '/scss/*.scss'))
      .pipe(bulk())
      .pipe(sass({
        outputStyle: 'compressed',
        includePaths: civicrmScssRoot.getPath(),
        precision: 10
      }).on('error', sass.logError))
      .pipe(stripCssComments({ preserve: false }))
      .pipe(gulp.dest(path.join(extPath, '/css/')));
  });

  gulp.task('sass:sync', function () {
    civicrmScssRoot.updateSync();
  });

  gulp.task('sass:watch', function () {
    var extPath = getExtensionPath();
    var watchPatterns = addExtensionCustomWatchPatternsToDefaultList([
      path.join(extPath, 'scss/**/*.scss')
    ], 'sass');

    gulp.watch(watchPatterns, ['sass']);
  });

  /**
   * Check if the current extension has a main *.scss file
   *
   * @return {Boolean}
   */
  function hasCurrentExtensionMainSassFile () {
    return !!find.fileSync(/\/scss\/([^/]+)?\.scss$/, getExtensionPath())[0];
  }
}());

// RequireJS
(function () {
  var originalExtension;
  var detectInstalled = require('detect-installed');
  var exec = require('child_process').exec;

  gulp.task('requirejs', function (cb) {
    // The original extension that the task was called with could change during
    // the execution, thus it gets saved so it can be restored later
    originalExtension = getCurrentExtension();

    requireJsTask(cb);
  });

  gulp.task('requirejs:watch', function () {
    var extPath = getExtensionPath();
    var watchPatterns = addExtensionCustomWatchPatternsToDefaultList([
      path.join(extPath, '**', 'src/**/*.js')
    ], 'requirejs');

    gulp.watch(watchPatterns, ['requirejs']).on('change', function (file) {
      try {
        test.for(file.path);
      } catch (ex) {
        test.all();
      }
    });
  });

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
    var buildFiles = find.fileSync(/js(\/[^/]+)?\/build\.js$/, path.join(__dirname, '..'));

    var sequence = buildFiles.filter(function (buildFile) {
      var content = fs.readFileSync(buildFile, 'utf8');

      return (new RegExp(getCurrentExtension(), 'g')).test(content);
    })
      .map(function (buildFileWithDependency) {
        var extension = getExtensionNameFromFile(buildFileWithDependency);

        return spawnTaskForExtension('requirejs', requireJsTask, extension);
      });

    sequence.length ? gulpSequence.apply(null, sequence)(function () {
      // Restore the original extension (used in the CLI) as the current extension
      // before marking the task as done
      setCurrentExtension(originalExtension);

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
        path: getExtensionPath(matches[2])
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
    var buildFilePath = find.fileSync('build.js', getExtensionPath())[0];
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
      throwError('requirejs', 'The `requirejs` package is not installed globally (http://requirejs.org/docs/optimization.html#download)');
    }

    sequence = addExtensionCustomTasksToSequence([
      spawnTaskForExtension('requirejs:main', requireJsMainTask, getCurrentExtension())
    ], 'requirejs');
    sequence.push(spawnTaskForExtension('requirejs:dependencies', extensionDependenciesTask, getCurrentExtension()));

    gulpSequence.apply(null, sequence)(cb);
  }
}());

// Test
(function () {
  gulp.task('test', function (cb) {
    var sequence = addExtensionCustomTasksToSequence(['test:main'], 'test');

    gulpSequence.apply(null, sequence)(cb);
  });

  gulp.task('test:main', function () {
    test.all();
  });

  gulp.task('test:watch', function () {
    var extPath = getExtensionPath();
    var watchPatterns = addExtensionCustomWatchPatternsToDefaultList([
      path.join(extPath, '**', 'test/**/*.spec.js'),
      '!' + path.join(extPath, '**', 'test/mocks/**/*.js'),
      '!' + path.join(extPath, '**', 'test/test-main.js')
    ], 'test');

    gulp.watch(watchPatterns).on('change', function (file) {
      test.single(file.path);
    });
  });
}());

// Watch
(function () {
  gulp.task('watch', ['sass:watch', 'requirejs:watch', 'test:watch']);
}());

// Build
(function () {
  gulp.task('build', function (cb) {
    gulpSequence('sass', 'requirejs', 'test')(cb);
  });
}());

/**
 * Given an original sequence of tasks and the name of the "wrapper" task
 * (requirejs, sass, etc), it finds if the current extension
 * has any custom tasks to add before/after or to straight replace the main task
 *
 * @param {Array} sequence
 * @param {String} taskName
 * @return {Array}
 */
function addExtensionCustomTasksToSequence (sequence, taskName) {
  var customTasks = getExtensionTasks(taskName);

  if (_.isFunction(customTasks.main)) {
    var mainIndex = _.findIndex(sequence, function (taskName) {
      return taskName.match(/:main$/);
    });

    gulp.task(sequence[mainIndex], customTasks.main);
    sequence.splice(mainIndex, 1, sequence[mainIndex]);
  }

  if (_.isArray(customTasks.pre)) {
    customTasks.pre.forEach(function (task, index) {
      gulp.task(task.name, task.fn);
      sequence.splice(index, 0, task.name);
    });
  }

  if (_.isArray(customTasks.post)) {
    _.each(customTasks.post, function (task) {
      gulp.task(task.name, task.fn);
      sequence.push(task.name);
    });
  }

  return sequence;
}

/**
 * Given a default list of watch patterns and the name of the "main" task
 * (requirejs, sass, etc) if finds if the current extension
 * has any custom task with custom watch patterns to add
 */
function addExtensionCustomWatchPatternsToDefaultList (defaultList, taskName) {
  return _(defaultList)
    .concat(getExtensionTasks(taskName).watchPatterns)
    .compact()
    .value();
}

/**
 * Returns the value of the "key" property of the <extension> tag and of the
 * <file> tag of the given info.xml file
 *
 * @param {String} infoFile
 * @return {Object}
 */
function getExtensionNameAndAliasFromInfoXML (infoFile) {
  var parsedXML = xml.parse(fs.readFileSync(infoFile, 'utf8'));

  var extensionTag = _.find(parsedXML, function (node) {
    return node.tagName && node.tagName === 'extension';
  });

  return {
    name: extensionTag.attributes.key,
    alias: _.find(extensionTag.childNodes, function (node) {
      return node.tagName && node.tagName === 'file';
    }).innerXML
  };
}

/**
 * Given a file, it finds the info.xml in one of the parent folders and returns
 * the extension name stored in it
 *
 * @param {String} filePath
 * @return {String}
 */
function getExtensionNameFromFile (filePath) {
  var infoXMLPath = findUp.sync('info.xml', { cwd: filePath });

  return getExtensionNameAndAliasFromInfoXML(infoXMLPath).name;
}

/**
 * Given a task name, it looks into the current extension's gulp-task/ folder
 * if there is any file with the name of the task
 */
function getExtensionTasks (taskName) {
  var filePath = path.join(getExtensionPath(), '/gulp-tasks/', taskName + '.js');

  if (fs.existsSync(filePath)) {
    return require(filePath)();
  } else {
    return {};
  }
}

/**
 * Returns the name of the extension currently used by the tasks
 *
 * If the name is not cached already, it will fetch the name from the CLI argument
 * and then cache it for the next time the function is called
 *
 * @return {String}
 */
function getCurrentExtension () {
  if (currentExtension) {
    return currentExtension;
  }

  currentExtension = getExtensionNameFromCLI();

  return currentExtension;
}

/**
 * Returns the extension name specified via CLI argument
 *
 * The name given can be either the real extension name or an alias, which matches
 * the value of the <file> tag in the info.xml file of the extension
 *
 * @return {String}
 * @throws
 *   Will throw an exception in case the argument has not been passed or
 *   no info.xml had been found for the given extension
 */
function getExtensionNameFromCLI () {
  var infoFiles, name;

  if (!argv.ext) {
    throwError('sass', 'Extension name not provided');
  }

  infoFiles = find.fileSync('info.xml', path.join(__dirname, '..'));

  for (var i = 0; i < infoFiles.length; i++) {
    var extensionData = getExtensionNameAndAliasFromInfoXML(infoFiles[i]);

    if (extensionData.name === argv.ext || _.endsWith(extensionData.alias, argv.ext)) {
      name = extensionData.name;
      break;
    }
  }

  if (!name) {
    throwError('sass', 'Extension "' + argv.ext + '" not found');
  }

  return name;
}

/**
 * Uses `cv` to get the path of the given extension
 *
 * @param {String} name If not provided, the name given via the CLI argument is used
 * @return {String}
 */
function getExtensionPath (name) {
  var extension = name || getCurrentExtension();

  return cv('path -x ' + extension)[0].value;
}

/**
 * Sets the given extension as the current one
 *
 * @param {String} extension
 */
function setCurrentExtension (extension) {
  currentExtension = extension;
}

/**
 * Spawns a task for an extension on the fly, using the name and function provided
 * The extension specified is set as the current extension before executing the task fn
 *
 * @param {String} taskName
 * @param {Function} taskFn
 * @param {String} ext
 */
function spawnTaskForExtension (taskName, taskFn, extension) {
  taskName += ' (' + extension + ')';

  gulp.task(taskName, function (cb) {
    setCurrentExtension(extension);
    taskFn(cb);
  });

  return taskName;
}

/**
 * A simple wrapper for displaying errors
 */
function throwError (plugin, msg) {
  throw new PluginError('Error', {
    message: colors.red(msg)
  });
}

var test = (function () {
  var find = require('find');
  var karma = require('karma');
  var replace = require('gulp-replace');
  var rename = require('gulp-rename');

  /**
   * Runs the karma server which does a single run of the test/s
   *
   * @param {string} configFile - The full path to the karma config file
   * @param {Function} cb - The callback to call when the server closes
   */
  function runServer (configFile, cb) {
    var reporters = argv.reporters ? argv.reporters.split(',') : ['progress'];

    new karma.Server({
      configFile: configFile,
      reporters: reporters,
      singleRun: true
    }, function () {
      cb && cb();
    }).start();
  }

  return {

    all: function () {
      var configFile = find.fileSync('karma.conf.js', getExtensionPath())[0];

      runServer(configFile);
    },
    for: function (srcFile) {
      var srcFileNoExt = path.basename(srcFile, path.extname(srcFile));

      var testFile = srcFile
        .replace(/src\/[^/]+\//, 'test/')
        .replace(srcFileNoExt + '.js', srcFileNoExt + '.spec.js');

      fs.statSync(testFile).isFile() && this.single(testFile);
    },
    single: function (testFile) {
      var configFilePath = findUp.sync('karma.conf.js', { cwd: testFile });
      var jsFolderPath = path.dirname(configFilePath);

      var tempConfigFile = 'karma.' + path.basename(testFile, path.extname(testFile)) + '.conf.temp.js';

      gulp
        .src(configFilePath)
        .pipe(replace('*.spec.js', path.basename(testFile)))
        .pipe(rename(tempConfigFile))
        .pipe(gulp.dest(jsFolderPath))
        .on('end', function () {
          runServer(path.join(jsFolderPath, tempConfigFile), function () {
            gulp
              .src(path.join(jsFolderPath, tempConfigFile), { read: false })
              .pipe(clean({ force: true }));
          });
        });
    }
  };
})();
