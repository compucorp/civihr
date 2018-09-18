/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  'use strict';

  FormLeaveController.$inject = ['$log', '$scope', 'utilsService'];

  function FormLeaveController ($log, $scope, utilsService) {
    $log.debug('Controller: FormLeaveController');

    var vm = {};

    vm.numberOfPublicHolidays = 0;

    /**
     * Initializes the controller by setting properties and adding watchers
     */
    (function init () {
      loadNumberOfPublicHolidays();
      initWatchers();
    }());

    /**
     * Loads the number of Public Holidays in Current Period
     */
    function loadNumberOfPublicHolidays () {
      utilsService.getNumberOfPublicHolidaysInCurrentPeriod()
        .then(function (number) {
          vm.numberOfPublicHolidays = number;
        });
    }

    /**
     * Attach listeners to $scope
     */
    function initWatchers () {
      $scope.$watch('entity.leave', toggleAddPublicHolidayRadios, true);
    }

    /**
     * This is a listener for when any of the leave types in the form changes.
     *
     * Only one leave type can have "add_public_holidays" selected. This function
     * checks if we have a type where its value changed from false to true and, if so,
     * set "add_public_holidays" to false for every other type.
     *
     * @param {Object} newValue - An object containing the new leave type values
     * @param {Object} oldValue - An object containing the old leave type values
     */
    function toggleAddPublicHolidayRadios (newValue, oldValue) {
      var newLeaveWithPublicHolidays = _.find(newValue, function (value, i) {
        return value.add_public_holidays && !oldValue[i].add_public_holidays;
      });

      if (newLeaveWithPublicHolidays) {
        newValue.forEach(function (value) {
          if (value.leave_type !== newLeaveWithPublicHolidays.leave_type) {
            value.add_public_holidays = false;
          }
        });
      }
    }

    return vm;
  }

  return { FormLeaveController: FormLeaveController };
});
