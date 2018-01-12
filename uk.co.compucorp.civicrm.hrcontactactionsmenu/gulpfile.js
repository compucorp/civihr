var civicrmScssRoot = require('civicrm-scssroot')();
var gulp = require('gulp');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');

gulp.task('sass', ['sass:sync'], function () {
  return gulp.src('scss/*.scss')
    .pipe(bulk())
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: civicrmScssRoot.getPath()
    }).on('error', sass.logError))
    .pipe(gulp.dest('css/'));
});

gulp.task('sass:sync', function () {
  civicrmScssRoot.updateSync();
});

gulp.task('watch', function () {
  gulp.watch('scss/**/*.scss', ['sass']);
});

gulp.task('default', ['sass', 'watch']);
