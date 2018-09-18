/* eslint-env amd */

define([], function () {
  LeaveTypeWizardFormController.$inject = ['$log'];

  return {
    __name: 'leaveTypeWizardForm',
    controller: LeaveTypeWizardFormController,
    controllerAs: 'form',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sourcePath + 'leave-type-wizard/form/components/leave-type-wizard-form.html';
    }]
  };

  function LeaveTypeWizardFormController ($log) {
    $log.debug('Controller: LeaveTypeWizardFormController');
  }
});
