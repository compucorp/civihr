var _ = require('lodash');
var argv = require('yargs').argv;
var colors = require('ansi-colors');
var cv = require('civicrm-cv')({ mode: 'sync' });
var find = require('find');
var findUp = require('find-up');
var fs = require('fs');
var gulp = require('gulp');
var path = require('path');
var PluginError = require('plugin-error');
var xml = require('xml-parse');

var currentExtension;
var extensionsPathCache = {};

module.exports = {
  addExtensionCustomTasksToSequence: addExtensionCustomTasksToSequence,
  addExtensionCustomWatchPatternsToDefaultList: addExtensionCustomWatchPatternsToDefaultList,
  getExtensionNameAndAliasFromInfoXML: getExtensionNameAndAliasFromInfoXML,
  getExtensionNameFromFile: getExtensionNameFromFile,
  getExtensionTasks: getExtensionTasks,
  getCurrentExtension: getCurrentExtension,
  getExtensionNameFromCLI: getExtensionNameFromCLI,
  getExtensionPath: getExtensionPath,
  setCurrentExtension: setCurrentExtension,
  spawnTaskForExtension: spawnTaskForExtension,
  throwError: throwError
};

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
      return taskName.match(/:main/);
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

  infoFiles = find.fileSync('info.xml', path.join(__dirname, '../', '../'));

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
 * The path is then cached as a performance optimization
 *
 * @param {String} name If not provided, the name given via the CLI argument is used
 * @return {String}
 */
function getExtensionPath (name) {
  var extension = name || getCurrentExtension();

  if (!extensionsPathCache[extension]) {
    extensionsPathCache[extension] = cv('path -x ' + extension)[0].value;
  }

  return extensionsPathCache[extension];
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
 * @param {String} ext If undefined, the current extension is used
 */
function spawnTaskForExtension (taskName, taskFn, extension) {
  extension = extension || getCurrentExtension();
  taskName += ' (' + extension + ')';

  gulp.task(taskName, function (cb) {
    setCurrentExtension(extension);

    return taskFn(cb);
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
