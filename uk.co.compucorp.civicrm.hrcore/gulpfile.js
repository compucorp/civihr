var gulp = require('gulp');
var gulpSequence = require('gulp-sequence');

// BackstopJS tasks
(function () {
  var tasks = require('./gulp/tasks/backstopjs');

  tasks.forEach(function (task) {
    gulp.task(task.name, task.fn);
  });
})();

// Sass
(function () {
  var tasks = require('./gulp/tasks/sass');

  tasks.forEach(function (task) {
    gulp.task(task.name, task.fn);
  });
}());

// RequireJS
(function () {
  var tasks = require('./gulp/tasks/requirejs');

  tasks.forEach(function (task) {
    gulp.task(task.name, task.fn);
  });
}());

// Test
(function () {
  var tasks = require('./gulp/tasks/test');

  tasks.forEach(function (task) {
    gulp.task(task.name, task.fn);
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
