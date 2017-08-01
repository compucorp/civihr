/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/modules/dialog',
  'common/services/check-permissions',
  'common/services/angular-date/date-format',
  'common/modules/directives',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/work-pattern-model',
  'leave-absences/shared/components/leave-calendar.component',
  'leave-absences/shared/components/leave-calendar-day.component',
  'leave-absences/shared/components/leave-calendar-legend.component',
  'leave-absences/shared/components/leave-calendar-month.component',
  'leave-absences/shared/components/leave-request-actions.component',
  'leave-absences/shared/components/leave-request-record-actions.component',
  'leave-absences/shared/components/leave-request-popup-comments-tab',
  'leave-absences/shared/components/leave-request-popup-files-tab',
  'leave-absences/shared/components/staff-leave-report',
  'leave-absences/shared/directives/leave-request-popup',
  'leave-absences/absence-tab/modules/config',
  'leave-absences/absence-tab/components/annual-entitlements',
  'leave-absences/absence-tab/components/absence-tab-container',
  'leave-absences/absence-tab/components/absence-tab-entitlements',
  'leave-absences/absence-tab/components/absence-tab-report',
  'leave-absences/absence-tab/components/absence-tab-work-patterns',
  'leave-absences/absence-tab/components/contract-entitlements'
], function (angular) {
  angular.module('absence-tab', [
    'ngResource',
    'ui.bootstrap',
    'textAngular',
    'common.angularDate',
    'common.dialog',
    'common.services',
    'common.directives',
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
    'leave-absences.directives',
    'absence-tab.config',
    'absence-tab.components'
  ]).run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
