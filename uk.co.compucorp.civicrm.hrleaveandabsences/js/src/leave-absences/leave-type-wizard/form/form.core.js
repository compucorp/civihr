/* eslint-env amd */

define([
  'common/angular',
  'common/directives/help-text.directive',
  'leave-absences/leave-type-wizard/leave-type-wizard.core'
], function (angular) {
  angular.module('leave-type-wizard.form.core', [
    'common.directives',
    'common.services',
    'leave-type-wizard.core'
  ]);
});
