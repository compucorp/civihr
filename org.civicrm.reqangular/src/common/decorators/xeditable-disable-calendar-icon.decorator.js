/* eslint-env amd */

define([], function () {
  'use strict';

  /**
   * Decorates the xeditable directive
   * Disables the show calendar button, as it is already added by uib-datepicker-popup
   */
  return ['$delegate', function ($delegate) {
    var directive = $delegate[0];
    var origCompile = directive.compile;

    directive.compile = function compile () {
      var link = origCompile.apply(this, arguments);

      return function (scope, element, attrs) {
        link.apply(this, arguments);
        // Disable the calendar icon added by xeditable, because the same is added
        // by uib-datepicker-popup
        attrs.eShowCalendarButton = false;
      };
    };

    return $delegate;
  }];
});
