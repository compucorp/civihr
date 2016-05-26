define([
  'common/vendor/perfect-scrollbar'
], function (ps) {
  'use strict';

  /**
   * Decorates ui-select-choices directive, in order for it to have custom scrollbars,
   * using the "perfect-scrollbar" plugin.
   */
  return ['$delegate', function ($delegate) {
    var directive = $delegate[0];
    var origCompile = directive.compile;
    directive.compile = function compile() {
      var link = origCompile.apply(this, arguments);
      return function (scope, element) {
        link.apply(this, arguments);
        if (element.closest('.civihr-ui-select').length) {
          ps.initialize(element[0]);
        }
      };
    };
    return $delegate;
  }];
});
