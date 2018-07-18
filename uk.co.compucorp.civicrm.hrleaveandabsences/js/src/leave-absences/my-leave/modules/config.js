/* global Drupal */
/* eslint-env amd */

(function (CRM, Drupal) {
  define([
    'common/angular',
    'common/lodash',
    'leave-absences/my-leave/modules/settings'
  ], function (angular, _) {
    return angular.module('my-leave.config', ['my-leave.settings'])
      .config([
        '$stateProvider', '$resourceProvider', '$urlRouterProvider', '$httpProvider',
        '$logProvider', '$analyticsProvider', 'settings',
        function ($stateProvider, $resourceProvider, $urlRouterProvider, $httpProvider,
          $logProvider, $analyticsProvider, settings) {
          configureAnalytics($analyticsProvider);

          $logProvider.debugEnabled(settings.debug);
          $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
          $resourceProvider.defaults.stripTrailingSlashes = false;
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

    /**
     * Configures Google Analytics via the angulartics provider
     *
     * @param {Object} $analyticsProvider
     */
    function configureAnalytics ($analyticsProvider) {
      $analyticsProvider.withAutoBase(true);
      $analyticsProvider.settings.ga = {
        userId: _.get(CRM, 'vars.session.contact_id')
      };
    }
  });
})(CRM, Drupal);
