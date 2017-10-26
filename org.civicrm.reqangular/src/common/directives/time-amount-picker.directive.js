/* eslint-env amd */

define([
  'common/modules/lodash',
  'common/modules/directives'
], function (_, directives) {
  'use strict';

  directives.directive('timeAmountPicker', ['$templateCache', function ($templateCache) {
    return {
      scope: {
        timeAmountPickerMinAmount: '<',
        timeAmountPickerMaxAmount: '<',
        timeAmountPickerInterval: '<',
        timeAmountPickerValue: '='
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
      startWatching();
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
     */
    function buildMinutesOptions () {
      var minute = 0;

      vm.minutesOptions = [];

      while (minute < 60) {
        var skip =
          vm.selectedHours !== '' &&
          ((+vm.selectedHours === Math.floor(minAmount) && minute < minAmount % 1 * 60) ||
           (+vm.selectedHours === Math.floor(maxAmount) && minute > maxAmount % 1 * 60));

        (!skip) && vm.minutesOptions.push(minute);

        minute += interval;
      }

      resetMinutesIfOutOfBounds();
    }

    /**
     * Builds hours and minutes options
     */
    function buildOptions () {
      interval = +$scope.timeAmountPickerInterval || 1;
      minAmount = +$scope.timeAmountPickerMinAmount || 0;
      maxAmount = +$scope.timeAmountPickerMaxAmount || 24;

      buildHoursOptions();
      buildMinutesOptions();
    }

    /**
     * Calculates the output humber in hours (float) and sets to the scope
     */
    function calculateSelectedValue () {
      if (vm.selectedHours === '' || vm.selectedMinutes === '') {
        return null;
      }

      $scope.timeAmountPickerValue = +vm.selectedHours + vm.selectedMinutes / 60;
    }

    /**
     * Starts watching inbound attributes for changes
     */
    function startWatching () {
      $scope.$watchGroup([
        'timeAmountPickerMinAmount',
        'timeAmountPickerMaxAmount',
        'timeAmountPickerInterval'
      ], function () {
        buildOptions();
      });
    }

    /**
     * Parses initially passed variable
     */
    function parseInitialValue () {
      if ($scope.timeAmountPickerValue) {
        vm.selectedHours = '' + Math.floor($scope.timeAmountPickerValue);
        vm.selectedMinutes = '' + Math.floor($scope.timeAmountPickerValue % 1 * 60);
      }

      calculateSelectedValue();
    }

    /**
     * Resets minutes if they appear out of bounds
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
