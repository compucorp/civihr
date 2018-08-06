var _ = require('lodash');
var find = require('find');
var gulp = require('gulp');

var utils = require('./gulp/utils');
var tasks = getMainTasks();

var watcherTasks = createTasksArray(['sass:watch', 'requirejs:watch', 'test:watch']);
var builderTasks = createTasksArray(['sass', 'requirejs', 'test']);

_.each(tasks, function (fn, name) {
  gulp.task(name, fn);
});

gulp.task('watch', gulp.parallel(watcherTasks));
gulp.task('build', gulp.series(builderTasks));

/**
 * Builds extension tasks functions collection
 *
 * @param  {Array} taskNames
 * @return {Array} of task functions
 */
function createTasksArray (taskNames) {
  return taskNames.map(function (taskName) {
    var fn = function (cb) {
      utils.spawnTaskForExtension(taskName, tasks[taskName], null, cb);
    };

    fn.displayName = taskName;

    return fn;
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
