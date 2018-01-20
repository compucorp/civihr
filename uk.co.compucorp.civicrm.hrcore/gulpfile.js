var _ = require('lodash');
var find = require('find');
var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');

var utils = require('./gulp/utils');

var tasks = getMainTasks();

_.each(tasks, function (fn, name) {
  gulp.task(name, fn);
});

gulp.task('watch', function (cb) {
  gulpSequence(
    utils.spawnTaskForExtension('sass:watch', tasks['sass:watch'], utils.getCurrentExtension()),
    utils.spawnTaskForExtension('requirejs:watch', tasks['requirejs:watch'], utils.getCurrentExtension()),
    utils.spawnTaskForExtension('test:watch', tasks['test:watch'], utils.getCurrentExtension())
  )(cb);
});

gulp.task('build', function (cb) {
  gulpSequence(
    utils.spawnTaskForExtension('sass', tasks['sass'], utils.getCurrentExtension()),
    utils.spawnTaskForExtension('requirejs', tasks['requirejs'], utils.getCurrentExtension()),
    utils.spawnTaskForExtension('test', tasks['test'], utils.getCurrentExtension())
  )(cb);
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
