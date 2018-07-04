/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/dropdown-button/components/calendar-feeds-dropdown-button.component',
  'common/services/check-permissions',
  'leave-absences/calendar-feeds/calendar-feeds.core',
  'leave-absences/calendar-feeds/calendar-feeds.models',
  'leave-absences/calendar-feeds/link-modal/link-modal.module'
], function (angular, CalendarFeedsDropdownButtonComponent) {
  angular.module('calendar-feeds.dropdown-button', [
    'common.services',
    'calendar-feeds.core',
    'calendar-feeds.models',
    'calendar-feeds.link-modal'
  ])
    .component(CalendarFeedsDropdownButtonComponent.__name, CalendarFeedsDropdownButtonComponent);
});
