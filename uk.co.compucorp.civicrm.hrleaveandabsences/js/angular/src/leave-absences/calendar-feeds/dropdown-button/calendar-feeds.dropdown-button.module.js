/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/dropdown-button/calendar-feeds.dropdown-button.component',
  'common/angularBootstrap',
  'leave-absences/calendar-feeds/calendar-feeds.core'
], function (angular, CalendarFeedsDropdownButtonComponent) {
  return angular.module('calendar-feeds.dropdown-button', [
    'ui.bootstrap',
    'calendar-feeds.core'
  ])
    .component(CalendarFeedsDropdownButtonComponent.__name, CalendarFeedsDropdownButtonComponent);
});
