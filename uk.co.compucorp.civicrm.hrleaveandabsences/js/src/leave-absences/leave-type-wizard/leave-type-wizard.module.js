/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/leave-type-wizard/components/leave-type-wizard.component',
  'common/directives/help-text.directive',
  'common/angular-spectrum-colorpicker',
  'leave-absences/shared/models/absence-type.model',
  'leave-absences/leave-type-wizard/leave-type-wizard.core'
], function (angular, LeaveTypeWizardComponent) {
  angular.module('leave-type-wizard', [
    'common.directives',
    'common.services',
    'leave-absences.models',
    'leave-type-wizard.core',
    'angularSpectrumColorpicker'
  ])
    .component(LeaveTypeWizardComponent);
});
