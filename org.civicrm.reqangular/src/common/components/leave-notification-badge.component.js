/* eslint-env amd */

define([
  'common/modules/components'
], function (components) {
  components.component('leaveNotificationBadge', {
    bindings: {
      filters: '<',
      refreshCountEventName: '<'
    },
    template: ['$templateCache', function ($templateCache) {
      return $templateCache.get('components/leave-notification-badge.html');
    }],
    controllerAs: 'badge',
    controller: LeaveNotificationBadgeController
  });

  LeaveNotificationBadgeController.$inject = ['$log', 'pubSub', 'LeaveRequest'];

  function LeaveNotificationBadgeController ($log, pubSub, LeaveRequest) {
    $log.debug('Component: leave-notification-badge');

    var vm = this;
    vm.count = 0;

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
      return LeaveRequest.all(vm.filters, null, null, null, false)
      .then(function (leaveRequests) {
        vm.count = leaveRequests.list.length;
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
