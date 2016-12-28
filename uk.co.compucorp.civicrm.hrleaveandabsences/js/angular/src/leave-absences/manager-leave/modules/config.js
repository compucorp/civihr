(function (CRM) {
  define([
    'common/angular',
    'leave-absences/manager-leave/modules/settings',
  ], function (angular) {
    return angular.module('manager-leave.config', ['manager-leave.settings'])
      .config([
        '$stateProvider', '$resourceProvider', '$urlRouterProvider', '$httpProvider', '$logProvider', 'settings',
        function ($stateProvider, $resourceProvider, $urlRouterProvider, $httpProvider, $logProvider, settings) {
          $logProvider.debugEnabled(settings.debug);

          $resourceProvider.defaults.stripTrailingSlashes = false;
          $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

          $urlRouterProvider.otherwise('/manager-leave/requests');
          $stateProvider
            .state('manager-leave', {
              abstract: true,
              url: '/manager-leave',
              template: '<manager-leave contact-id="$resolve.contactId"></manager-leave>',
              resolve: {
                contactId: function () {
                  return CRM.vars.leaveAndAbsences.contactId;
                }
              }
            })
            .state('manager-leave.requests', {
              url: '/requests',
              template: '<manager-leave-requests contact-id="$ctrl.contactId"></manager-leave-requests>'
            })
            .state('manager-leave.calendar', {
              url: '/calendar',
              template: '<manager-leave-calendar contact-id="$ctrl.contactId"></manager-leave-calendar>'
            });
        }
      ]);
  });
})(CRM);
