/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/shared/modules/shared-settings'
], function (angular) {
  angular.module('leave-type-wizard.core', [
    'leave-absences.settings'
  ]);
});
