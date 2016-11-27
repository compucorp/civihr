define([
  'common/angular',
  'leave-absences/shared/ui-router',
  'leave-absences/my-leave/modules/settings',
  'leave-absences/my-leave/components/my-leave'
], function (angular) {
  angular.module('my-leave', [
      'ngResource',
      'my-leave.settings',
      'my-leave.components'
    ])
    .config(['$resourceProvider', '$httpProvider', '$logProvider', 'settings',
      function ($resourceProvider, $httpProvider, $logProvider, settings) {
        $logProvider.debugEnabled(settings.debug);
        $resourceProvider.defaults.stripTrailingSlashes = false;
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
      }
    ])
    .run(['$log', function ($log) {
      $log.debug('app.run');
    }]);

  return angular;
});
