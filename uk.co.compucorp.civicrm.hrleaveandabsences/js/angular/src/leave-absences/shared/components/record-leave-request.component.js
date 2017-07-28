/* eslint-env amd */

define([
  'leave-absences/shared/modules/components',
  'common/services/hr-settings'
], function (components) {
  components.component('recordLeaveRequest', {
    bindings: {
      btnClass: '@',
      contactId: '<',
      selectedContactId: '<',
      isSelfRecord: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/record-leave-request.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: record-leave-request');

    var vm = this;

    vm.leaveRequestOptions = [
      { type: 'leave', icon: 'briefcase', label: 'Leave' },
      { type: 'sickness', icon: 'stethoscope', label: 'Sickness' },
      { type: 'toil', icon: 'calendar-plus-o', label: 'Overtime' }
    ];
  }
});
