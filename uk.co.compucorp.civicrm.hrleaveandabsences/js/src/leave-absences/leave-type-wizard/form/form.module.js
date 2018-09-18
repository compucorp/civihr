/* eslint-env amd */

define([
  'common/angular',
  'leave-absences/leave-type-wizard/form/components/leave-type-wizard-form.component',
  'leave-absences/leave-type-wizard/form/form.core'
], function (angular, LeaveTypeWizardFormComponent) {
  angular.module('leave-type-wizard.form', [
    'leave-type-wizard.form.core'
  ])
    .component(LeaveTypeWizardFormComponent.__name, LeaveTypeWizardFormComponent);
});
