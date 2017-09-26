/* eslint-env amd */

define([
  'common/services/detect-device.service'
], function () {
  'use strict';

  /**
   * Decorates the uib-datepicker-popup directive,
   * Adds a Calendar icon
   * Opens the calendar on click of both the input and the button
   */
  return ['$delegate', 'detectDevice', function ($delegate, detectDevice) {
    var directive = $delegate[0];
    var origCompile = directive.compile;

    directive.compile = function compile () {
      var link = origCompile.apply(this, arguments);

      return function (scope, element, attrs) {
        link.apply(this, arguments);

        addCalendarIcon(element, scope);
        openCalendarOnClick(element, scope);
      };
    };

    /**
     * Adds calendar icon beside the input and adds events to it
     *
     * @param inputElement
     * @param scope
     */
    function addCalendarIcon (inputElement, scope) {
      var calendarIcon = angular.element('<span ' +
        'class="input-group-addon pointer">' +
        '<i class="fa fa-calendar"></i>' +
        '</span>');

      openCalendarOnClick(calendarIcon, scope);

      inputElement.after(calendarIcon);
    }

    /**
     * Open Calendar icon on click of given element
     *
     * @param element
     * @param scope
     */
    function openCalendarOnClick (element, scope) {
      if (!detectDevice.isMobile()) {
        element.on('click', onClick);

        scope.$on('$destroy', function () {
          element.off('click', onClick);
        });
      }

      function onClick () {
        scope.isOpen = true;
        scope.$apply();
      }
    }

    return $delegate;
  }];
});
