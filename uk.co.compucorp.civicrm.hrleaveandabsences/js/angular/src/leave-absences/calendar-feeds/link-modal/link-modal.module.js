/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/link-modal/component/calendar-feeds-link-modal.component',
  'leave-absences/calendar-feeds/link-modal/services/calendar-feeds-link-modal.service',
  'leave-absences/calendar-feeds/link-modal/directives/input-with-copy-button.directive',
  'leave-absences/calendar-feeds/calendar-feeds.core',
  'leave-absences/calendar-feeds/link-modal/link-modal.core',
  'leave-absences/calendar-feeds/link-modal/link-modal.constants'
], function (angular, CalendarFeedsLinkModalComponent, CalendarFeedsLinkModalService,
  inputWithCopyButton) {
  angular.module('calendar-feeds.link-modal', [
    'calendar-feeds.core',
    'calendar-feeds.link-modal.core',
    'calendar-feeds.link-modal.constants'
  ])
    .component(CalendarFeedsLinkModalComponent.__name, CalendarFeedsLinkModalComponent)
    .directive(inputWithCopyButton.__name, inputWithCopyButton)
    .factory(CalendarFeedsLinkModalService.__name, CalendarFeedsLinkModalService);
});
