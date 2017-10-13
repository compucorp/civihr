/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap',
  'common/text-angular',
  'common/directives/loading',
  'common/modules/dialog',
  'common/services/angular-date/date-format',
  'common/services/check-permissions',
  'leave-absences/shared/ui-router',
  'leave-absences/shared/models/absence-period.model',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/shared/components/leave-balance-tab.component',
  'leave-absences/shared/components/leave-calendar.component',
  'leave-absences/shared/components/leave-calendar-day.component',
  'leave-absences/shared/components/leave-calendar-legend.component',
  'leave-absences/shared/components/leave-calendar-month.component',
  'leave-absences/shared/components/leave-request-actions.component',
  'leave-absences/shared/components/leave-request-popup-comments-tab.component',
  'leave-absences/shared/components/leave-request-popup-details-tab.component',
  'leave-absences/shared/components/leave-request-popup-files-tab',
  'leave-absences/shared/components/leave-request-record-actions.component',
  'leave-absences/shared/components/manage-leave-requests.component',
  'leave-absences/shared/controllers/sub-controllers/leave-request.controller',
  'leave-absences/shared/controllers/sub-controllers/sick-request.controller',
  'leave-absences/shared/controllers/sub-controllers/toil-request.controller',
  'leave-absences/shared/models/absence-period.model',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/shared/services/leave-popup.service',
  'leave-absences/manager-leave/components/manager-leave-container',
  'leave-absences/manager-leave/modules/config'
], function (angular) {
  angular.module('manager-leave', [
    'ngResource',
    'ngAnimate',
    'ui.bootstrap',
    'ui.router',
    'ui.select',
    'textAngular',
    'common.angularDate',
    'common.dialog',
    'common.models',
    'common.directives',
    'common.mocks',
    'leave-absences.models',
    'leave-absences.components',
    'leave-absences.controllers',
    'leave-absences.models',
    'leave-absences.services',
    'manager-leave.config',
    'manager-leave.components'
  ])
  .run(['$log', '$rootScope', 'shared-settings', 'settings', function ($log, $rootScope, sharedSettings, settings) {
    $log.debug('app.run');

    $rootScope.sharedPathTpl = sharedSettings.sharedPathTpl;
    $rootScope.settings = settings;
  }]);

  return angular;
});
