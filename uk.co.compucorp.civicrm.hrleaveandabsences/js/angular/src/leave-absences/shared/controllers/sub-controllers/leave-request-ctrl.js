define([
  'leave-absences/shared/modules/controllers',
  'leave-absences/shared/controllers/request-ctrl',
  'leave-absences/shared/models/instances/leave-request-instance',
], function (controllers) {
  controllers.controller('LeaveRequestCtrl', [
    '$controller', '$log', '$uibModalInstance', 'directiveOptions', 'LeaveRequestInstance',
    function ($controller, $log, $modalInstance, directiveOptions, LeaveRequestInstance) {
      $log.debug('LeaveRequestCtrl');

      var parentRequestCtrl = $controller('RequestCtrl'),
        vm = Object.create(parentRequestCtrl);

      parentRequestCtrl.directiveOptions = directiveOptions;
      parentRequestCtrl.$modalInstance = $modalInstance;
      vm.leaveType = 'leave';

      /**
       * Initializes the controller on loading the dialog
       */
      (function initController() {
        vm.loading.absenceTypes = true;
        initRequest();

        parentRequestCtrl._init()
          .finally(function () {
            vm.loading.absenceTypes = false;
          });
      })();

      /**
       * Initialize leaverequest based on attributes that come from directive
       */
      function initRequest() {
        var attributes = parentRequestCtrl._initRequestAttributes();

        parentRequestCtrl.request = LeaveRequestInstance.init(attributes);
      }

      return vm;
    }
  ]);
});
