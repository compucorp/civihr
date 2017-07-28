/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/modules/dialog',
  'common/modules/directives',
  'common/services/check-permissions',
  'common/services/angular-date/date-format',
  'leave-absences/absence-tab/modules/config',
  'leave-absences/shared/components/leave-request-actions.component',
  'leave-absences/absence-tab/components/absence-tab-container',
  'leave-absences/absence-tab/components/absence-tab-report',
  'leave-absences/absence-tab/components/absence-tab-entitlements',
  'leave-absences/absence-tab/components/absence-tab-work-patterns',
  'leave-absences/absence-tab/components/contract-entitlements',
  'leave-absences/absence-tab/components/annual-entitlements',
  'leave-absences/shared/components/leave-request-popup-comments-tab',
  'leave-absences/shared/components/leave-request-popup-files-tab',
  'leave-absences/shared/components/staff-leave-report',
  'leave-absences/shared/components/staff-leave-calendar',
  'leave-absences/shared/components/record-leave-request.component',
  'leave-absences/shared/directives/leave-request-popup',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/calendar-model',
  'leave-absences/shared/models/leave-request-model',
  'leave-absences/shared/models/work-pattern-model',
  'leave-absences/shared/models/absence-type-model',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/modules/shared-settings'
], function (angular) {
  angular.module('absence-tab', [
    'ngResource',
    'ui.bootstrap',
    'textAngular',
    'common.angularDate',
    'common.dialog',
    'common.directives',
    'common.services',
    /*
     * @TODO Because the app requires Contact, which requires Group,
     * which requires api.group.mock and api.group-contact.mock,
     * we need to include 'common.mocks' in the production app.
     * This needs to be refactored.
     */
    'common.mocks',
    'absence-tab.config',
    'absence-tab.components',
    'leave-absences.components',
    'leave-absences.directives',
    'leave-absences.models',
    'leave-absences.settings'
  ]).run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
