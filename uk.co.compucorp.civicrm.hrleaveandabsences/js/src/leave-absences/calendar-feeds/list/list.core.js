/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/link-modal/link-modal.module'
], function (angular) {
  angular.module('calendar-feeds.list.core', [
    'calendar-feeds.link-modal'
  ]);
});
