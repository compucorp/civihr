/* eslint-env amd */

define([], function () {
  'use strict';

  /**
   * Focus UI-Select when clicked on empty space of the element
   */
  return ['$delegate', function ($delegate) {
    var directive = $delegate[0];
    var origCompile = directive.compile;

    directive.compile = function compile() {
      var link = origCompile.apply(this, arguments);

      return function (scope, element, attrs, ngModel) {
        var ctrl = ngModel[0];

        if (ctrl.multiple) {
          element.click(function (e) {
            var clickedOnEmptySpace =
              angular.element(e.target).parents('.ui-select-match').length === 0 &&
              angular.element(e.target).parents('.ui-select-choices').length === 0;

            if (clickedOnEmptySpace) {
              element.find('.ui-select-search').focus();
              ctrl.open = true;
              scope.$apply();
            }
          });
        }
        link.apply(this, arguments);
      };
    };

    return $delegate;
  }];
});
