var gulp = require('gulp');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var civicrmScssRoot = require('civicrm-scssroot')();

gulp.task('sass', function () {
  civicrmScssRoot.updateSync();
  gulp.src('scss/*.scss')
    .pipe(bulk())
    .pipe(sass({
      includePaths: civicrmScssRoot.getPath(),
      outputStyle: 'compressed'
    }).on('error', sass.logError))
    .pipe(gulp.dest('css/'));
});

gulp.task('watch', function () {
    gulp.watch('scss/**/*.scss', ['sass']);
});

gulp.task('default', ['sass', 'watch']);;
