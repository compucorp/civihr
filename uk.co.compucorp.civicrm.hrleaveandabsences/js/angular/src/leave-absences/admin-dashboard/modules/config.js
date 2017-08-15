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
            template: '<manage-leave-requests contact-id="$root.settings.contactId"></manage-leave-requests>'
          })
          .state('calendar', {
            url: '/calendar',
            template: '<leave-calendar contact-id="$root.settings.contactId"></leave-calendar>'
          })
          .state('balance-report', {
            url: '/balance-report',
            template: '<leave-balance-tab></leave-balance-tab>'
          });
      }
    ]);
});
