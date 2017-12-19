var gulp = require('gulp');
var postcss = require('gulp-postcss');
var postcssPrefix = require('postcss-prefix-selector');
var transformSelectors = require('gulp-transform-selectors');

var bootstrapNamespace = '#bootstrap-theme';
var outsideNamespaceRegExp = /^\.___outside-namespace/;

module.exports = function (SubTask) {
  return {
    post: [
      {
        name: 'sass:namespace',
        fn: function () {
          var cssDir = __dirname + '/../' + 'css/';

          return gulp.src(cssDir +  '*.css')
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

function removeOutsideNamespaceMarker (selector) {
  return outsideNamespaceRegExp.test(selector) ? selector.replace(outsideNamespaceRegExp, '') : selector;
}
