/* eslint-env amd */

define([
  'leave-absences/shared/modules/components',
  'common/services/hr-settings'
], function (components) {
  components.component('leaveRequestCreateDropdown', {
    bindings: {
      btnClass: '@',
      contactId: '<',
      selectedContactId: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-request-create-dropdown.html';
    }],
    controllerAs: 'vm',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: leave-request-create-dropdown');

    var vm = this;

    vm.leaveRequestOptions = [
      { type: 'leave', icon: 'briefcase', label: 'Leave' },
      { type: 'sickness', icon: 'stethoscope', label: 'Sickness' },
      { type: 'toil', icon: 'calendar-plus-o', label: 'Overtime' }
    ];
  }
});
