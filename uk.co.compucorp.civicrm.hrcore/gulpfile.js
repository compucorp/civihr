var _ = require('lodash');
var find = require('find');
var gulp = require('gulp');

var utils = require('./gulp/utils');
var tasks = getMainTasks();

var watcherPromises = buildTaskPromises(['sass:watch', 'requirejs:watch', 'test:watch']);
var builderPromises = buildTaskPromises(['sass', 'requirejs', 'test']);

_.each(tasks, function (fn, name) {
  gulp.task(name, fn);
});

gulp.task('watch', gulp.series(watcherPromises));
gulp.task('build', gulp.series(builderPromises));

/**
 * Builds extension tasks promises
 *
 * @param  {Array} taskNames
 * @return {Array} of task promises
 */
function buildTaskPromises (taskNames) {
  return taskNames.map(function (taskName) {
    return utils.spawnTaskForExtension(taskName, tasks[taskName]);
  });
}

/**
 * Gets all the task listed in the files under the gulp/task folder
 *
 * @return {Object}
 */
function getMainTasks () {
  return _(find.fileSync('gulp/tasks'))
    .map(function (taskFile) {
      return require('./' + taskFile);
    })
    .flatten()
    .map(function (task) {
      return [task.name, task.fn];
    })
    .fromPairs()
    .value();
}
