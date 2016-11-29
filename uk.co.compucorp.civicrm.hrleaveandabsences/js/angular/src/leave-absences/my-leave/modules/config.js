(function (CRM) {
  define([
    'common/angular',
    'leave-absences/my-leave/modules/settings',
  ], function (angular) {
    return angular.module('my-leave.config', ['my-leave.settings'])
      .config([
        '$stateProvider', '$resourceProvider', '$urlRouterProvider', '$httpProvider', '$logProvider', 'settings',
        function ($stateProvider, $resourceProvider, $urlRouterProvider, $httpProvider, $logProvider, settings) {
          $logProvider.debugEnabled(settings.debug);

          $resourceProvider.defaults.stripTrailingSlashes = false;
          $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

          $urlRouterProvider.otherwise('/my-leave/report');
          $stateProvider
            .state('my-leave', {
              abstract: true,
              url: '/my-leave',
              template: '<my-leave contact-id="$resolve.contactId"></my-leave>',
              resolve: {
                contactId: function () {
                  return CRM.vars.leaveAndAbsences.contactId;
                }
              }
            })
            .state('my-leave.report', {
              url: '/report',
              templateUrl: settings.pathTpl + "report.html"
            })
            .state('my-leave.calendar', {
              url: '/calendar',
              template: '<h3>Calendar</h3>'
            });
        }
      ]);
  });
})(CRM);
