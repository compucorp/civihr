var gulp = require('gulp');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var postcss = require('gulp-postcss');
var postcssPrefix = require('postcss-prefix-selector');
var postcssDiscardDuplicates = require('postcss-discard-duplicates');
var stripCssComments = require('gulp-strip-css-comments');

gulp.task('sass', function () {
  gulp.src('scss/*.scss')
    .pipe(bulk())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(stripCssComments({ preserve: false }))
    .pipe(gulp.dest('css/'));
});

gulp.task('watch', function () {
    gulp.watch('scss/**/*.scss', ['sass']);
});

gulp.task('default', ['sass', 'watch']);;
