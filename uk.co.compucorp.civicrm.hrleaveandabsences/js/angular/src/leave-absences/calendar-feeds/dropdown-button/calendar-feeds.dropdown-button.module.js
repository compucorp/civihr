/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/dropdown-button/calendar-feeds-dropdown-button.component',
  'common/angularBootstrap',
  'leave-absences/calendar-feeds/calendar-feeds.core',
  'leave-absences/calendar-feeds/calendar-feed/calendar-feeds.calendar-feed.module',
  'leave-absences/calendar-feeds/link-modal/calendar-feeds.link-modal.module'
], function (angular, CalendarFeedsDropdownButtonComponent) {
  return angular.module('calendar-feeds.dropdown-button', [
    'ui.bootstrap',
    'calendar-feeds.core',
    'calendar-feeds.link-modal',
    'calendar-feeds.models'
  ])
    .component(CalendarFeedsDropdownButtonComponent.__name, CalendarFeedsDropdownButtonComponent);
});
