/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/calendar-feeds/link-modal/calendar-feeds.link-modal.module'
], function (angular) {
  angular.module('calendar-feeds', [
    'calendar-feeds.link-modal'
  ])
    .run(['$log', function ($log) {
      $log.debug('app.run');
    }]);

  return angular;
});
