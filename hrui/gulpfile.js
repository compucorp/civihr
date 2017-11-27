var gulp = require('gulp');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var uglify = require('gulp-uglify');

gulp.task('js-bundle', function () {
  gulp.src('js/src/**/*.js')
    .pipe(sourcemaps.init())
    .pipe(uglify())
    .pipe(concat('hrui.min.js'))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest('js/dist'));
});

gulp.task('watch', function () {
  gulp.watch('js/src/**/*.js', ['js-bundle']);
});

gulp.task('default', ['js-bundle']);
