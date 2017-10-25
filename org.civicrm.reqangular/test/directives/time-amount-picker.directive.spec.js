/* eslint-env amd */

define([
  'common/moment',
  'common/modules/directives'
], function (moment, directives) {
  'use strict';

  directives.directive('timepickerSelect', ['$templateCache', function ($templateCache) {
    return {
      scope: {

        timeAmountPickerValue: '=',
        timeAmountPickerTimeTo: '<',
        timeAmountPickerInterval: '<'
      },
      restrict: 'A',
      controllerAs: 'selector',
      controller: ['$scope', timepickerSelectController],
      template: $templateCache.get('timepicker-select.html')
    };
  }]);

  timeAmountPickerSelectController.$inject = ['$scope', 'notificationService'];

  function timepickerSelectController ($scope) {
    var vm = this;

    vm.placeholder = $scope.timepickerSelectPlaceholder;
    vm.options = [];

    /**
     * Builds options for the selector
     */
    function buildOptions () {
      var interval = +$scope.timepickerSelectInterval || 1;
      var timeFrom = moment.duration($scope.timepickerSelectTimeFrom || '00:00');
      var timeTo = moment.duration($scope.timepickerSelectTimeTo || '23:59');

      vm.options = [];

      while (timeFrom.asMinutes() <= timeTo.asMinutes()) {
        var time = moment.utc(timeFrom.asMilliseconds());

        vm.options.push(time.format('HH:mm'));
        timeFrom.add(interval, 'minutes');
      }
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
