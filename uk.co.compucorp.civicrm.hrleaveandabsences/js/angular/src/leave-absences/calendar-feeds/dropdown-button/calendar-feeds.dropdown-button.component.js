/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  return {
    __name: 'calendarFeedsDropdownButton',
    bindings: {
      dropdownPosition: '@'
    },
    controller: CalendarFeedsDropdownButtonController,
    controllerAs: 'dropdownButton',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sourcePath + 'calendar-feeds/dropdown-button/calendar-feeds.dropdown-button.html';
    }]
  };

  function CalendarFeedsDropdownButtonController () {
    var vm = this;

    vm.feeds = [];
    vm.loading = {
      feeds: false
    };
  }
});
