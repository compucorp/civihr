define([
  'common/angular',
  'leave-absences/shared/ui-router'
], function (angular) {
  angular.module('my-leave', [
      'ngResource'
    ])
    .config(['$resourceProvider', '$httpProvider', '$logProvider',
      function ($resourceProvider, $httpProvider, $logProvider) {
        $logProvider.debugEnabled(true);
        $resourceProvider.defaults.stripTrailingSlashes = false;
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
      }
    ])
    .run(['$log', function ($log) {
      $log.debug('app.run');
    }]);

  return angular;
});
