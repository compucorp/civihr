define([
  'common/lodash',
  'job-contract/controllers/controllers'
], function (_, controllers) {
  'use strict';

  controllers.controller('FormLeaveCtrl', [
    '$scope', '$log', 'UtilsService',
    function ($scope, $log, UtilsService) {
      $log.debug('Controller: FormLeaveCtrl');

      var vm = {};

      vm.numberOfPublicHolidays = 0;

      init();

      /**
       * Initializes the controller by setting properties and adding watchers
       */
      function init() {
        loadNumberOfPublicHolidays();
        addListeners();
      }

      /**
       * Loads the number of Public Holidays in Current Period
       */
      function loadNumberOfPublicHolidays() {
        UtilsService.getNumberOfPublicHolidaysInCurrentPeriod()
          .then(function (number) {
            vm.numberOfPublicHolidays = number;
          });
      }

      /**
       * Attach listeners to $scope
       */
      function addListeners() {
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
            if (value.leave_type != newLeaveWithPublicHolidays.leave_type) {
              value.add_public_holidays = false;
            }
          });
        }
      }

      return vm;
    }]);
});
