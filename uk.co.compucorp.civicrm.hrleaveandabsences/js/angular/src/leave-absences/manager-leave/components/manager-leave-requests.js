define([
  'leave-absences/manager-leave/modules/components',
  'leave-absences/shared/models/leave-request-model',
], function (components) {

  components.component('managerLeaveRequests', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/manager-leave-requests.html';
    }],
    controllerAs: 'requests',
    controller: ['$log', 'LeaveRequest', controller]
  });


  function controller($log, LeaveRequest) {
    $log.debug('Component: manager-leave-requests', this.contactId);
    var vm = {};
    vm.contactId = this.contactId;
    vm.leaveRequests = [];

    LeaveRequest.all()
      .then(function(allLeaveRequests){
        vm.leaveRequests = allLeaveRequests.list;
      })
    return vm;
  }
});
