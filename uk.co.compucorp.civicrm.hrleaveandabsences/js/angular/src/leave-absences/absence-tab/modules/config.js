(function (CRM) {
  define([
    'common/angular',
    'leave-absences/absence-tab/modules/settings',
  ], function (angular) {
    return angular.module('absence-tab.config', ['absence-tab.settings'])
      .config([
        '$stateProvider', '$resourceProvider', '$urlRouterProvider', '$httpProvider', '$logProvider', 'settings',
        function ($stateProvider, $resourceProvider, $urlRouterProvider, $httpProvider, $logProvider, settings) {
          $logProvider.debugEnabled(settings.debug);

          $resourceProvider.defaults.stripTrailingSlashes = false;
          $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

          $urlRouterProvider.otherwise('/absence-tab/report');
          $stateProvider
            .state('absence-tab', {
              abstract: true,
              url: '/absence-tab',
              template: '<absence-tab></absence-tab>'
            })
            .state('absence-tab.report', {
              url: '/report',
              template: '<absence-tab-report></absence-tab-report>'
            })
            .state('absence-tab.calendar', {
              url: '/report',
              template: '<absence-tab-calendar></absence-tab-calendar>'
            })
        }
      ]);
  });
})(CRM);
