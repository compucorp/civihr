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
                },
                format: ['DateFormat', function (DateFormat) {
                  // stores the data format in HR_setting.DATE_FORMAT
                  return DateFormat.getDateFormat();
                }]
              }
            })
            .state('my-leave.report', {
              url: '/report',
              template: '<my-leave-report contact-id="myleave.contactId"></my-leave-report>'
            })
            .state('my-leave.calendar', {
              url: '/calendar',
              template: '<my-leave-calendar contact-id="myleave.contactId"></my-leave-calendar>'
            });
        }
      ]);
  });
})(CRM);
