(function (CRM) {
  define([
    'common/angular',
    'leave-absences/absence-tab/modules/settings',
  ], function (angular) {
    return angular.module('absence-tab.config', ['absence-tab.settings'])
      .config([
        '$resourceProvider', '$httpProvider', '$logProvider', 'settings',
        function ($resourceProvider, $httpProvider, $logProvider, settings) {
          $logProvider.debugEnabled(settings.debug);

          $resourceProvider.defaults.stripTrailingSlashes = false;
          $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        }
      ]);
  });
})(CRM);
