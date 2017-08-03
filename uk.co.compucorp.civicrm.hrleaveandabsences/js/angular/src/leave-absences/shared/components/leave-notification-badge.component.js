/* eslint-env amd */

define([
  'leave-absences/shared/modules/components'
], function (components) {
  components.component('leaveNotificationBadge', {
    bindings: {
      filters: '<',
      refreshCountEventName: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-notification-badge.html';
    }],
    controllerAs: 'badge',
    controller: LeaveNotificationBadgeController
  });

  LeaveNotificationBadgeController.$inject = ['$log', 'pubSub', 'LeaveRequest'];

  function LeaveNotificationBadgeController ($log, pubSub, LeaveRequest) {
    $log.debug('Component: leave-notification-badge');

    var vm = this;
    vm.count = 0;
    vm.loading = { count: true };

    (function init () {
      initListeners();
      fetchCount();
    })();

    /**
     * Fetch count of leave requests which matches the filter
     *
     * @return {Promise}
     */
    function fetchCount () {
      vm.loading.count = true;

      return LeaveRequest.all(vm.filters, null, null, null, false)
        .then(function (leaveRequests) {
          vm.count = leaveRequests.list.length;
          vm.loading.count = false;
        });
    }

    /**
     * Initializes the event listeners
     */
    function initListeners () {
      pubSub.subscribe(vm.refreshCountEventName, fetchCount);
    }
  }
});
