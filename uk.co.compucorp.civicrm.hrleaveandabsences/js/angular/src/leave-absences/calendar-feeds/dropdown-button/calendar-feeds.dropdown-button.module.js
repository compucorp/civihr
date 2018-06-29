/* eslint-env amd */

define([
  'common/angular',
  './calendar-feeds-dropdown-button.component',
  '../calendar-feed/calendar-feeds.calendar-feed.module',
  'common/angularBootstrap',
  '../calendar-feeds.core'
], function (angular, CalendarFeedsDropdownButtonComponent) {
  return angular.module('calendar-feeds.dropdown-button', [
    'ui.bootstrap',
    'calendar-feeds.core',
    'calendar-feeds.models'
  ])
    .component(CalendarFeedsDropdownButtonComponent.__name, CalendarFeedsDropdownButtonComponent);
});
