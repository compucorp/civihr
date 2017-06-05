var gulp = require('gulp');
var bulk = require('gulp-sass-bulk-import');
var sass = require('gulp-sass');
var postcss = require('gulp-postcss');
var postcssPrefix = require('postcss-prefix-selector');
var stripCssComments = require('gulp-strip-css-comments');
var transformSelectors = require('gulp-transform-selectors');
var civicrmScssRoot = require('civicrm-scssroot')();

var bootstrapNamespace = '#bootstrap-theme';

gulp.task('sass', ['sass:sync'], function () {
  return gulp.src('scss/*.scss')
    .pipe(bulk())
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: civicrmScssRoot.getPath(),
      precision: 10
    }).on('error', sass.logError))
    .pipe(stripCssComments({ preserve: false }))
    .pipe(postcss([postcssPrefix({
      prefix: bootstrapNamespace + ' ',
      exclude: [/^html/, /^body/]
    })]))
    .pipe(transformSelectors(namespaceRootElements, { splitOnCommas: true }))
    .pipe(gulp.dest('css/'));
});

gulp.task('sass:sync', function () {
  civicrmScssRoot.updateSync();
});

gulp.task('watch', function () {
  gulp.watch('scss/**/*.scss', ['sass']);
});

gulp.task('default', ['sass', 'watch']);

/**
 * Apply the namespace on html and body elements
 *
 * @param  {string} selector the current selector to be transformed
 * @return string
 */
function namespaceRootElements (selector) {
  var regex = /^(body|html)/;

  if (regex.test(selector)) {
    selector = selector.replace(regex, function (match) {
      return match + bootstrapNamespace;
    }) + ',\n' + selector.replace(regex, bootstrapNamespace);
  }

  return selector;
}
