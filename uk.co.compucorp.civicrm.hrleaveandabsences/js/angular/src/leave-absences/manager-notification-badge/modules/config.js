/* eslint-env amd */

(function (CRM) {
  define([
    'common/angular',
    'leave-absences/manager-notification-badge/modules/settings'
  ], function (angular) {
    return angular.module('manager-notification-badge.config', ['manager-notification-badge.settings'])
      .config([
        '$resourceProvider', '$httpProvider', '$logProvider', 'settings',
        function ($resourceProvider, $httpProvider, $logProvider, settings) {
          $logProvider.debugEnabled(settings.debug);

          $resourceProvider.defaults.stripTrailingSlashes = false;
          $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        }
      ]);
  });
})(CRM);
