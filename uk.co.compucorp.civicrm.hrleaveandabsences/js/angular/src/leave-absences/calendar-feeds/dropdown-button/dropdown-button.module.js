/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/dropdown-button/calendar-feeds-dropdown-button.component',
  'leave-absences/calendar-feeds/calendar-feeds.core',
  'leave-absences/calendar-feeds/calendar-feeds.models',
  'leave-absences/calendar-feeds/link-modal/link-modal.module',
  'leave-absences/calendar-feeds/dropdown-button/dropdown-button.core'
], function (angular, CalendarFeedsDropdownButtonComponent) {
  return angular.module('calendar-feeds.dropdown-button', [
    'calendar-feeds.core',
    'calendar-feeds.models',
    'calendar-feeds.link-modal',
    'calendar-feeds.dropdown-button.core'
  ])
    .component(CalendarFeedsDropdownButtonComponent.__name, CalendarFeedsDropdownButtonComponent);
});
