/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/directives'
], function (_, directives) {
  'use strict';

  directives.directive('timeAmountPicker', ['$templateCache', function ($templateCache) {
    return {
      scope: {
        minAmount: '<timeAmountPickerMinAmount',
        maxAmount: '<timeAmountPickerMaxAmount',
        interval: '<timeAmountPickerInterval',
        value: '=timeAmountPickerValue'
      },
      restrict: 'E',
      controllerAs: 'picker',
      controller: ['$scope', timeAmountPickerController],
      template: $templateCache.get('time-amount-picker.html')
    };
  }]);

  timeAmountPickerController.$inject = ['$scope'];

  function timeAmountPickerController ($scope) {
    var interval, minAmount, maxAmount;
    var vm = this;

    vm.hoursOptions = [];
    vm.minutesOptions = [];
    vm.selectedHours = '';
    vm.selectedMinutes = '';

    vm.buildMinutesOptions = buildMinutesOptions;
    vm.calculateSelectedValue = calculateSelectedValue;

    (function init () {
      parseInitialValue();
      watchTimeAmountPickerOptions();
    })();

    /**
     * Builds hours options
     */
    function buildHoursOptions () {
      var hour = Math.floor(minAmount);

      vm.hoursOptions = [];

      while (hour <= maxAmount) {
        vm.hoursOptions.push(hour);

        hour++;
      }
    }

    /**
     * Builds minutes options
     * Skips minutes if the are out of bounds for edge hours,
     *   for example minutes 0-29 will be skipped for an hour of 9
     *   if minimum time allowed is 9:30
     */
    function buildMinutesOptions () {
      var skip, isLessThanLowerBound, isMoreThanUpperBound;
      var minute = 0;

      vm.minutesOptions = [];

      while (minute < 60) {
        isLessThanLowerBound = +vm.selectedHours === Math.floor(minAmount) && minute < minAmount % 1 * 60;
        isMoreThanUpperBound = +vm.selectedHours === Math.floor(maxAmount) && minute > maxAmount % 1 * 60;
        skip = vm.selectedHours !== '' && (isLessThanLowerBound || isMoreThanUpperBound);

        (!skip) && vm.minutesOptions.push(minute);

        minute += interval;
      }

      resetMinutesIfOutOfBounds();
    }

    /**
     * Builds hours and minutes options
     */
    function buildOptions () {
      interval = +$scope.interval || 1;
      minAmount = +$scope.minAmount || 0;
      maxAmount = +$scope.maxAmount || 24;

      buildHoursOptions();
      buildMinutesOptions();
    }

    /**
     * Calculates the output number in hours (float) and sets to the scope
     */
    function calculateSelectedValue () {
      if (vm.selectedHours === '' || vm.selectedMinutes === '') {
        return null;
      }

      $scope.value = +vm.selectedHours + vm.selectedMinutes / 60;
    }

    /**
     * Starts watching inbound attributes for changes
     */
    function watchTimeAmountPickerOptions () {
      $scope.$watchGroup(['minAmount', 'maxAmount', 'interval'], function () {
        buildOptions();
      });
    }

    /**
     * Parses initially passed variable
     */
    function parseInitialValue () {
      if ($scope.value !== undefined) {
        vm.selectedHours = '' + Math.floor($scope.value);
        vm.selectedMinutes = '' + Math.floor($scope.value % 1 * 60);
      }

      calculateSelectedValue();
    }

    /**
     * Resets minutes if they appear out of bounds
     * For example, if the maximum time allowed is 9:30,
     *   currently selected time is 5:45, then by changing hours
     *   from 5 to 9 will reset minutes from 45 to 30, otherwise,
     *   a not-allowed time could be selected (9:45)
     */
    function resetMinutesIfOutOfBounds () {
      if (vm.selectedMinutes < vm.minutesOptions[0]) {
        vm.selectedMinutes = '' + vm.minutesOptions[0];
      } else
      if (vm.selectedMinutes > _.last(vm.minutesOptions)) {
        vm.selectedMinutes = '' + _.last(vm.minutesOptions);
      }
    }
  }
});
