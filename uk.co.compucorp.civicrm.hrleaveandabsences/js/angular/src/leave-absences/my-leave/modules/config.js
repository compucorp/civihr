/* eslint-env amd */

(function (CRM) {
  define([
    'common/angular',
    'leave-absences/my-leave/modules/settings'
  ], function (angular) {
    return angular.module('my-leave.config', ['my-leave.settings'])
      .config([
        '$stateProvider', '$resourceProvider', '$urlRouterProvider', '$httpProvider', '$logProvider', 'settings',
        function ($stateProvider, $resourceProvider, $urlRouterProvider, $httpProvider, $logProvider, settings) {
          $logProvider.debugEnabled(settings.debug);

          $resourceProvider.defaults.stripTrailingSlashes = false;
          $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

          $urlRouterProvider.otherwise('/my-leave/report');
          $stateProvider
            .state('my-leave', {
              abstract: true,
              url: '/my-leave',
              template: '<my-leave-container contact-id="$resolve.contactId"></my-leave-container>',
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
              url: '/report?leave-request-id',
              template: '<staff-leave-report contact-id="myleave.contactId"></staff-leave-report>',
              onEnter: [
                '$stateParams', 'LeavePopup', function ($stateParams, LeavePopup) {
                  if ($stateParams['leave-request-id']) {
                    LeavePopup.openModalByID($stateParams['leave-request-id']);
                  }
                }
              ]
            })
            .state('my-leave.calendar', {
              url: '/calendar',
              template: '<leave-calendar contact-id="myleave.contactId" role-override="staff"></leave-calendar>'
            });
        }
      ]);
  });
})(CRM);
