/* eslint-env amd */

define([
  'common/modules/directives'
], function (directives) {
  'use strict';

  directives.directive('timepickerSelect', ['$templateCache', function ($templateCache) {
    return {
      scope: {
        timepickerSelectDefaultOption: '<',
        timepickerSelectTimeFrom: '<',
        timepickerSelectTimeTo: '<',
        timepickerSelectInterval: '<'
      },
      restrict: 'A',
      controllerAs: 'selector',
      controller: ['$scope', timepickerSelectController],
      template: $templateCache.get('timepicker-select.html')
    };
  }]);

  timepickerSelectController.$inject = ['$scope', 'notificationService'];

  function timepickerSelectController ($scope) {
    var vm = this;

    vm.defaultOption = $scope.timepickerSelectDefaultOption;
    vm.options = [];

    /**
     * Builds options for the selector
     */
    function buildOptions () {
      var hour, minute;
      var interval = +$scope.timepickerSelectInterval || 1;
      var timeFrom = calculateLimit($scope.timepickerSelectTimeFrom || '00:00');
      var timeTo = calculateLimit($scope.timepickerSelectTimeTo || '23:59');

      vm.options = [];

      for (var minutes = timeFrom; minutes <= timeTo; minutes += interval) {
        hour = addTrailingZero(Math.floor(minutes / 60));
        minute = addTrailingZero(minutes % 60);

        vm.options.push(hour + ':' + minute);
      }
    }

    /**
     * Calculates a limit in minutes by a given time
     *
     * @param  {String} time in "HH:MM" format
     * @return {Integer}
     */
    function calculateLimit (time) {
      time = time.split(':');

      return (+time[0]) * 60 + (+time[1]);
    }

    /**
     * Adds a trailing zero if a number is less than 10
     */
    function addTrailingZero (number) {
      number += '';

      return (number.length === 1 ? '0' : '') + number;
    }

    $scope.$watchGroup([
      'timepickerSelectTimeFrom',
      'timepickerSelectTimeTo',
      'timepickerSelectInterval'
    ], function () {
      buildOptions();
    });
  }
});
