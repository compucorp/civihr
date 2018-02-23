/* eslint-env amd */
(function ($) {
  define([
    'common/angular',
    'common/angularBootstrap',
    'common/text-angular',
    'common/directives/time-amount-picker.directive',
    'common/directives/timepicker-select.directive',
    'common/filters/angular-date/format-date',
    'common/filters/time-unit-applier.filter',
    'common/modules/dialog',
    'common/modules/directives',
    'common/services/check-permissions',
    'common/services/angular-date/date-format',
    'common/services/notification.service',
    'common/angularUiRouter',
    'leave-absences/shared/modules/shared-settings',
    'leave-absences/shared/models/absence-type.model',
    'leave-absences/shared/models/calendar.model',
    'leave-absences/shared/models/entitlement.model',
    'leave-absences/shared/models/leave-request.model',
    'leave-absences/shared/models/work-pattern.model',
    'leave-absences/shared/components/leave-calendar.component',
    'leave-absences/shared/components/leave-calendar-day.component',
    'leave-absences/shared/components/leave-calendar-legend.component',
    'leave-absences/shared/components/leave-calendar-month.component',
    'leave-absences/shared/components/leave-request-actions.component',
    'leave-absences/shared/components/leave-request-popup-comments-tab.component',
    'leave-absences/shared/components/leave-request-popup-details-tab.component',
    'leave-absences/shared/components/leave-request-popup-files-tab',
    'leave-absences/shared/components/leave-request-record-actions.component',
    'leave-absences/shared/components/staff-leave-report.component',
    'leave-absences/shared/controllers/sub-controllers/request-modal-details-leave.controller',
    'leave-absences/shared/controllers/sub-controllers/request-modal-details-sickness.controller',
    'leave-absences/shared/controllers/sub-controllers/request-modal-details-toil.controller',
    'leave-absences/shared/models/absence-type.model',
    'leave-absences/shared/models/calendar.model',
    'leave-absences/shared/models/leave-request.model',
    'leave-absences/shared/models/work-pattern.model',
    'leave-absences/shared/models/absence-type.model',
    'leave-absences/shared/models/entitlement.model',
    'leave-absences/shared/modules/shared-settings',
    'leave-absences/shared/services/leave-popup.service',
    'leave-absences/absence-tab/components/absence-tab-container.component',
    'leave-absences/absence-tab/components/absence-tab-entitlements.component',
    'leave-absences/absence-tab/components/absence-tab-work-patterns.component',
    'leave-absences/absence-tab/components/annual-entitlement-change-log.component',
    'leave-absences/absence-tab/components/annual-entitlements.component',
    'leave-absences/absence-tab/components/contract-entitlements.component',
    'leave-absences/absence-tab/modules/config'
  ], function (angular) {
    angular.module('absence-tab', [
      'ngResource',
      'ui.bootstrap',
      'ui.router',
      'textAngular',
      'common.angularDate',
      'common.dialog',
      'common.directives',
      'common.filters',
      'common.services',
      /*
       * @TODO Because the app requires Contact, which requires Group,
       * which requires api.group.mock and api.group-contact.mock,
       * we need to include 'common.mocks' in the production app.
       * This needs to be refactored.
       */
      'common.mocks',
      'leave-absences.settings',
      'leave-absences.models',
      'leave-absences.components',
      'leave-absences.controllers',
      'leave-absences.models',
      'leave-absences.services',
      'leave-absences.settings',
      'absence-tab.config',
      'absence-tab.components'
    ]).run(['$log', '$rootScope', 'shared-settings', 'settings', '$state', '$urlService', '$stateRegistry',
      function ($log, $rootScope, sharedSettings, settings, $state, $urlService, $stateRegistry) {
        $log.debug('app.run');

        $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
        $rootScope.settings = settings;

        addstates();

        $('#mainTabContainer')
          .on('tabsbeforeactivate', function (e, ui) {
            var id = ui.newTab.attr('id');

            if (id !== 'tab_absence') {
              delstates();
            } else {
              addstates();
            }
          });

        function addstates () {
          $urlService.rules.otherwise('/absence-tab/report');

          $stateRegistry.register({
            name: 'absence-tab',
            abstract: true,
            url: '/absence-tab',
            template: '<absence-tab-container></absence-tab-container>'
          });
          $stateRegistry.register({
            name: 'absence-tab.report',
            url: '/report',
            template: '<staff-leave-report contact-id="$root.settings.contactId"></staff-leave-report>'
          });
          $stateRegistry.register({
            name: 'absence-tab.calendar',
            url: '/calendar',
            template: '<leave-calendar contact-id="$root.settings.contactId" role-override="staff"></leave-calendar>'
          });
          $stateRegistry.register({
            name: 'absence-tab.entitlements',
            url: '/entitlements',
            template: '<absence-tab-entitlements contact-id="$root.settings.contactId"></absence-tab-entitlements>'
          });
          $stateRegistry.register({
            name: 'absence-tab.work-patterns',
            url: '/work-patterns',
            template: '<absence-tab-work-patterns contact-id="$root.settings.contactId"></absence-tab-work-patterns>'
          });

          console.log('after addstates: ', $state.get());
        }

        function delstates () {
          $stateRegistry.dispose();
          $urlService.rules.otherwise(function () {});

          console.log('after delstates: ', $state.get());
        }
      }]);

    return angular;
  });
}(CRM.$));
