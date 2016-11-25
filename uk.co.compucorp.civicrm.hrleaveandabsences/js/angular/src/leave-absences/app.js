define([
  'common/angular',
], function (angular) {
  angular.module('appraisals', [])
  .config(['$stateProvider', '$urlRouterProvider', '$resourceProvider', '$httpProvider', '$logProvider',
    function ($stateProvider, $urlRouterProvider, $resourceProvider, $httpProvider, $logProvider) {
      $logProvider.debugEnabled(true);
      $resourceProvider.defaults.stripTrailingSlashes = false;
      $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

      routes($urlRouterProvider, $stateProvider);
    }
  ])
  .run(['$log', 'editableOptions', 'editableThemes',
    function ($log, editableOptions, editableThemes) {
      $log.debug('app.run');

      editableOptions.theme = 'bs3';
    }
  ]);

  return angular;
});
