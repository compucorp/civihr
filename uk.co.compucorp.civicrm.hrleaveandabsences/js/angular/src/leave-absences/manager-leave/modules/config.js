/* global Drupal */
/* eslint-env amd */

(function (CRM, Drupal) {
  define([
    'common/angular',
    'common/lodash',
    'leave-absences/manager-leave/modules/settings'
  ], function (angular, _) {
    return angular.module('manager-leave.config', ['manager-leave.settings'])
      .config([
        '$stateProvider', '$resourceProvider', '$urlRouterProvider', '$httpProvider',
        '$logProvider', '$analyticsProvider', 'settings',
        function ($stateProvider, $resourceProvider, $urlRouterProvider, $httpProvider,
          $logProvider, $analyticsProvider, settings) {
          $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
          $resourceProvider.defaults.stripTrailingSlashes = false;
          $urlRouterProvider.otherwise('/manager-leave/requests');

          configureAnalytics($analyticsProvider);
          $logProvider.debugEnabled(settings.debug);

          $stateProvider
            .state('manager-leave', {
              abstract: true,
              url: '/manager-leave',
              template: '<manager-leave-container contact-id="$resolve.contactId"></manager-leave-container>',
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
            .state('manager-leave.requests', {
              url: '/requests?leave-request-id',
              template: '<manage-leave-requests contact-id="managerLeave.contactId"></manage-leave-requests>',
              onEnter: [
                '$stateParams', 'LeavePopup', function ($stateParams, LeavePopup) {
                  if ($stateParams['leave-request-id']) {
                    LeavePopup.openModalByID($stateParams['leave-request-id']);
                  }
                }
              ]
            })
            .state('manager-leave.calendar', {
              url: '/calendar',
              template: '<leave-calendar contact-id="managerLeave.contactId"></leave-calendar>'
            })
            .state('manager-leave.leave-balances', {
              url: '/leave-balances',
              template: '<leave-balance-tab></leave-balance-tab>'
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
