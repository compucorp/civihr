/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/absence-tab/modules/settings'
], function (angular) {
  return angular.module('absence-tab.config', ['absence-tab.settings'])
    .config([
      '$urlRouterProvider', '$stateProvider', '$resourceProvider', '$httpProvider', '$logProvider', 'settings',
      function ($urlRouterProvider, $stateProvider, $resourceProvider, $httpProvider, $logProvider, settings) {
        $logProvider.debugEnabled(settings.debug);

        $resourceProvider.defaults.stripTrailingSlashes = false;
        $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

        $stateProvider
          .state('absence-tab', {
            abstract: true,
            url: '/absence-tab',
            template: '<absence-tab-container></absence-tab-container>'
          })
          .state('absence-tab.report', {
            url: '/report',
            template: '<staff-leave-report contact-id="$root.settings.contactId"></staff-leave-report>'
          })
          .state('absence-tab.calendar', {
            url: '/calendar',
            template: '<leave-calendar contact-id="$root.settings.contactId" role-override="staff"></leave-calendar>'
          })
          .state('absence-tab.entitlements', {
            url: '/entitlements',
            template: '<absence-tab-entitlements contact-id="$root.settings.contactId"></absence-tab-entitlements>'
          })
          .state('absence-tab.work-patterns', {
            url: '/work-patterns',
            template: '<absence-tab-work-patterns contact-id="$root.settings.contactId"></absence-tab-work-patterns>'
          });
      }
    ]);
});
