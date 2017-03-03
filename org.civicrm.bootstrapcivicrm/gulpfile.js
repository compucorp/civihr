var gulp = require('gulp');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var postcss = require('gulp-postcss');
var postcssPrefix = require('postcss-prefix-selector');
var postcssDiscardDuplicates = require('postcss-discard-duplicates');
var stripCssComments = require('gulp-strip-css-comments');

gulp.task('sass-bootstrap', function () {
  gulp.src('scss/bootstrap/bootstrap.scss')
    .pipe(bulk())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(stripCssComments({ preserve: false }))
    .pipe(gulp.dest('css/'));
});

gulp.task('sass-civicrm', function () {
  gulp.src('scss/civicrm/custom-civicrm.scss')
    .pipe(bulk())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(stripCssComments({ preserve: false }))
    .pipe(postcss([postcssPrefix({
      prefix: '.crm-container ',
      exclude: [/page-civicrm/, /crm-container/, /civicrm-menu/, /#root-menu-div/]
    }), postcssDiscardDuplicates]))
    .pipe(gulp.dest('css/'));
});

gulp.task('sass', ['sass-bootstrap', 'sass-civicrm']);

gulp.task('watch', function () {
  gulp.watch('scss/**/*.scss', ['sass']);
});

gulp.task('default', ['sass', 'watch']);
