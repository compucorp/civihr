/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  'use strict';

  FormLeaveController.$inject = ['$log', '$scope', 'utilsService'];

  function FormLeaveController ($log, $scope, utilsService) {
    $log.debug('Controller: FormLeaveController');

    var vm = this;

    vm.$onInit = $onInit;
    vm.numberOfPublicHolidays = 0;

    function $onInit () {
      loadNumberOfPublicHolidays();
    }

    /**
     * Loads the number of Public Holidays in Current Period
     */
    function loadNumberOfPublicHolidays () {
      utilsService.getNumberOfPublicHolidaysInCurrentPeriod()
        .then(function (number) {
          vm.numberOfPublicHolidays = number;
        });
    }
  }

  return { FormLeaveController: FormLeaveController };
});
