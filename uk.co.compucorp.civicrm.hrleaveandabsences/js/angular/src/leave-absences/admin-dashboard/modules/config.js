/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/admin-dashboard/modules/settings'
], function (angular) {
  return angular.module('admin-dashboard.config', ['admin-dashboard.settings'])
    .config([
      '$stateProvider', '$resourceProvider', '$urlRouterProvider', '$httpProvider', '$logProvider', 'settings',
      function ($stateProvider, $resourceProvider, $urlRouterProvider, $httpProvider, $logProvider, settings) {
        $logProvider.debugEnabled(settings.debug);

        $resourceProvider.defaults.stripTrailingSlashes = false;
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

        $urlRouterProvider.otherwise('/requests');
        $stateProvider
          .state('requests', {
            url: '/requests',
            template: '<admin-dashboard-requests></admin-dashboard-requests>'
          })
          .state('calendar', {
            url: '/calendar',
            template: '<admin-dashboard-calendar></admin-dashboard-calendar>'
          });
      }
    ]);
});
