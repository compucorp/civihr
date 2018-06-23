/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/link-modal/calendar-feeds-link-modal.service',
  'common/angularBootstrap',
  'leave-absences/calendar-feeds/calendar-feeds.core'
], function (angular, CalendarFeedsLinkModalService) {
  return angular.module('calendar-feeds.link-modal', [
    'ui.bootstrap',
    'calendar-feeds.core'
  ])
    .factory(CalendarFeedsLinkModalService.__name, CalendarFeedsLinkModalService);
});
