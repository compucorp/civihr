var gulp = require('gulp');
var path = require('path');
var postcss = require('gulp-postcss');
var postcssPrefix = require('postcss-prefix-selector');
var transformSelectors = require('gulp-transform-selectors');

var bootstrapNamespace = '#bootstrap-theme';
var outsideNamespaceRegExp = /^\.___outside-namespace/;

module.exports = function () {
  return {
    post: [
      {
        name: 'sass:namespace',
        fn: function () {
          var cssDir = path.join(__dirname, '..', 'css/');

          return gulp.src(path.join(cssDir, '*.css'))
            .pipe(postcss([postcssPrefix({
              prefix: bootstrapNamespace + ' ',
              exclude: [
                /^html/, /^body/, /page-civi/, /crm-container/, outsideNamespaceRegExp
              ]
            })]))
            .pipe(transformSelectors(namespaceRootElements, { splitOnCommas: true }))
            .pipe(transformSelectors(removeOutsideNamespaceMarker, { splitOnCommas: true }))
            .pipe(gulp.dest(cssDir));
        }
      }
    ]
  };
};

/**
 * Apply the namespace on <html> and <body> elements
 *
 * @param  {String} selector the current selector to be transformed
 * @return {String}
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

/**
 * Deletes the special class that was used as marker for styles that should
 * not be nested inside the bootstrap namespace from the given selector
 *
 * @param  {String} selector
 * @return {String}
 */
function removeOutsideNamespaceMarker (selector) {
  return selector.replace(outsideNamespaceRegExp, '');
}
