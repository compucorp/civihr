/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/modules/dialog',
  'common/models/option-group',
  'common/directives/loading',
  'common/services/check-permissions',
  'common/services/angular-date/date-format',
  'leave-absences/shared/ui-router',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/models/absence-period.model',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/shared/models/calendar.model',
  'leave-absences/shared/models/entitlement.model',
  'leave-absences/shared/models/entitlement.model',
  'leave-absences/shared/models/leave-request.model',
  'leave-absences/shared/models/public-holiday.model',
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
  'leave-absences/shared/controllers/sub-controllers/leave-request.controller',
  'leave-absences/shared/controllers/sub-controllers/sick-request.controller',
  'leave-absences/shared/controllers/sub-controllers/toil-request.controller',
  'leave-absences/shared/models/absence-period.model',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/shared/models/calendar.model',
  'leave-absences/shared/models/entitlement.model',
  'leave-absences/shared/models/leave-request.model',
  'leave-absences/shared/models/public-holiday.model',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/services/leave-popup.service',
  'leave-absences/my-leave/components/my-leave-container.component',
  'leave-absences/my-leave/modules/config'
], function (angular) {
  angular.module('my-leave', [
    'ngResource',
    'ngAnimate',
    'ui.bootstrap',
    'ui.router',
    'textAngular',
    'common.angularDate',
    'common.dialog',
    'common.directives',
    'common.mocks',
    'common.models',
    'common.services',
    'leave-absences.components',
    'leave-absences.controllers',
    'leave-absences.models',
    'leave-absences.services',
    'leave-absences.settings',
    'my-leave.components',
    'my-leave.config'
  ])
  .run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
