/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/leave-type-wizard/leave-type-wizard.constants',
  'leave-absences/shared/modules/shared-settings'
], function (angular) {
  angular.module('leave-type-wizard.core', [
    'leave-absences.settings',
    'leave-type-wizard.constants'
  ]);
});
