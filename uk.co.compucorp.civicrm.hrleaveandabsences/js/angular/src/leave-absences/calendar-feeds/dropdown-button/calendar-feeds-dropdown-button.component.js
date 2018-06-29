/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/calendar-feeds/calendar-feed/calendar-feeds.calendar-feed.module'
], function (_) {
  CalendarFeedsDropdownButtonController.$inject = ['CalendarFeed'];

  return {
    __name: 'calendarFeedsDropdownButton',
    bindings: {
      dropdownPosition: '@'
    },
    controller: CalendarFeedsDropdownButtonController,
    controllerAs: 'dropdownButton',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sourcePath + 'calendar-feeds/dropdown-button/calendar-feeds-dropdown-button.html';
    }]
  };

  function CalendarFeedsDropdownButtonController (CalendarFeed) {
    var vm = this;

    vm.feeds = [];
    vm.loading = {
      feeds: false
    };

    (function init () {
      loadFeeds();
    }());

    /**
     * Loads Calendar Feeds
     *
     * @return {Promise}
     */
    function loadFeeds () {
      vm.loading.feeds = true;

      return CalendarFeed.all()
        .then(function (feeds) {
          vm.feeds = feeds;
        })
        .finally(function () {
          vm.loading.feeds = false;
        });
    }
  }
});
