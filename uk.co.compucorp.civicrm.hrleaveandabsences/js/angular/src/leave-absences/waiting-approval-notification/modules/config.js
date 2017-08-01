/* eslint-env amd */

(function (CRM) {
  define([
    'common/angular',
    'leave-absences/waiting-approval-notification/modules/settings'
  ], function (angular) {
    return angular.module('waiting-approval-notification.config', ['waiting-approval-notification.settings'])
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
