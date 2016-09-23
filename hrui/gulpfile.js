var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');

gulp.task('js-bundle', function () {
  gulp.src('js/src/**/*.js')
    .pipe(uglify())
    .pipe(concat('hrui.min.js'))
    .pipe(gulp.dest('js/dist'));
});

gulp.task('watch', function () {
  gulp.watch('js/src/**/*.js', ['js-bundle']);
});

gulp.task('default', ['js-bundle']);
