define([
  'leave-absences/my-leave/modules/components'
], function (components) {
  components.component('myLeaveContainer', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-container.html';
    }],
    controllerAs: 'myleave',
    controller: ['$log', '$scope', '$uibModal', 'settings', function ($log, $scope, $modal, settings) {
      $log.debug('Component: my-leave-container');

      var vm = {};
      vm.leaveRequest = {
        fromDate: new Date(),
        toDate: new Date(),
        showDatePickerFrom: false,
        showDatePickerTo: false,
        isChangeExpanded: false,
        selectedResponse: "1",
        isAdmin: false
      };

      vm.showModal = function () {
        $modal.open({
          templateUrl: settings.pathTpl + 'components/my-leave-request.html',
          //TODO The controller needs to be moved a separate file when implementing the logic
          controller: ['leaveRequest', '$uibModalInstance', function(leaveRequest, modalInstance){
            var vm = {};

            vm.leaveRequest = leaveRequest;

            vm.closeModal = function () {
              modalInstance.close();
            };

            return vm;
          }],
          controllerAs: 'modal',
          resolve: {
            leaveRequest: function () {
              return vm.leaveRequest;
            }
          }
        });
      };

      return vm;
    }]
  });
});
