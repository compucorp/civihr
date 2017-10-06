/* eslint-env amd */

define([
  'common/moment',
  'common/services/detect-device.service'
], function (moment) {
  'use strict';

  /**
   * Decorates the uib-datepicker-popup directive,
   * Switches to HTML5 datepicker if mobile version is detected
   */
  return ['$delegate', 'detectDevice', function ($delegate, detectDevice) {
    var directive = $delegate[0];
    var origCompile = directive.compile;

    directive.require = ['ngModel', 'uibDatepickerPopup'];
    directive.compile = function () {
      var link = origCompile.apply(this, arguments);

      return function (scope, element, attrs, ctrl) {
        if (!detectDevice.isMobile()) {
          link.apply(this, arguments);
        } else {
          element.parent('.input-group').css('display', 'block');
          element.prop('type', 'date');
          attrs.ngReadonly = false;

          ctrl[0].$formatters.push(function (viewValue) {
            return convertDateToHTML5DatepickerFormat(viewValue);
          });

          scope.$watch('datepickerOptions.minDate', function (value) {
            attrs.$set('min', convertDateToHTML5DatepickerFormat(value));
          });
          scope.$watch('datepickerOptions.maxDate', function (value) {
            attrs.$set('max', convertDateToHTML5DatepickerFormat(value));
          });
        }
      };
    };

    /**
     * Converts given date object to the format suitable for HTML5 Date picker
     *
     * @param {Object} date
     * @return {string}
     */
    function convertDateToHTML5DatepickerFormat (date) {
      return date ? moment(date).format('Y-MM-DD') : '';
    }

    return $delegate;
  }];
});
