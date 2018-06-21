/* eslint-env amd */

define(function () {
  'use strict';

  CalendarFeedListsController.$inject = ['CalendarFeedConfig'];

  return {
    __name: 'calendarFeedsList',
    controller: CalendarFeedListsController,
    controllerAs: 'list',
    templateUrl: ['shared-settings', function (settings) {
      return settings.sourcePath + 'calendar-feeds/list/calendar-feeds-list.html';
    }]
  };

  function CalendarFeedListsController (CalendarFeedConfig) {
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
