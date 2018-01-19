var _ = require('lodash');
var find = require('find');
var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');

var tasks = getMainTasks();

_.each(tasks, function (fn, name) {
  gulp.task(name, fn);
});

gulp.task('watch', ['sass:watch', 'requirejs:watch', 'test:watch']);

gulp.task('build', function (cb) {
  gulpSequence('sass', 'requirejs', 'test')(cb);
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
