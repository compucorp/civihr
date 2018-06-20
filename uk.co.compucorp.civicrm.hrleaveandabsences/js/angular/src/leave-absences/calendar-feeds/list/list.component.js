/* eslint-env amd */

define(function () {
  'use strict';

  CalendarFeedListController.$inject = ['CalendarFeedConfig'];

  return {
    __name: 'calendarFeedList',
    controller: CalendarFeedListController,
    controllerAs: 'list',
    templateUrl: ['shared-settings', function (settings) {
      return settings.sourcePath + 'calendar-feeds/list/list.html';
    }]
  };

  function CalendarFeedListController (CalendarFeedConfig) {
    var vm = this;

    vm.calendarFeedConfigs = [];

    (function init () {
      loadCalendarFeedConfigs();
    })();

    /**
     * Loads and stores calendar feed configurations.
     *
     * @return {Promise} resolves to void.
     */
    function loadCalendarFeedConfigs () {
      return CalendarFeedConfig.all()
        .then(function (calendarFeedConfigs) {
          vm.calendarFeedConfigs = calendarFeedConfigs;
        });
    }
  }
});
