var _ = require('lodash');
var find = require('find');
var gulp = require('gulp');

var utils = require('./gulp/utils');
var tasks = getMainTasks();

_.each(tasks, function (fn, name) {
  gulp.task(name, fn);
});

gulp.task('watch', function (cb) {
  var watchTasksNames = spawnTasks(['sass:watch', 'requirejs:watch', 'test:watch']);

  gulp.parallel(watchTasksNames)(cb);
});
gulp.task('build', function (cb) {
  var buildTasksNames = spawnTasks(['sass', 'requirejs', 'test']);

  gulp.series(buildTasksNames)(cb);
});

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

/**
 * Spawns tasks for the selected extension
 *
 * @param  {Array} taskNames
 * @return {Array} of updated tasks names as per the selected extension
 */
function spawnTasks (taskNames) {
  return taskNames.map(function (taskName) {
    return utils.spawnTaskForExtension(taskName, tasks[taskName]);
  });
}
