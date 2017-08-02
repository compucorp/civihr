/* eslint-env amd */

define([
  'leave-absences/shared/modules/components'
], function (components) {
  components.component('leaveNotificationBadge', {
    bindings: {
      eventName: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-notification-badge.html';
    }],
    controllerAs: 'badge',
    controller: ['$log', '$rootScope', 'pubSub', 'LeaveRequest', controller]
  });

  function controller ($log, $rootScope, pubSub, LeaveRequest) {
    $log.debug('Component: leave-notification-badge');

    var filters = {};
    var vm = this;
    vm.count = 0;
    vm.loading = { count: true };

    (function init () {
      initializeListeners();
    })();

    /**
     * Fetch count of leave requests which matches the filter
     *
     * @return {Promise}
     */
    function fetchCount () {
      vm.loading.count = true;

      return LeaveRequest.all(filters)
        .then(function (leaveRequests) {
          vm.count = leaveRequests.list.length;
          vm.loading.count = false;
        });
    }

    /**
     * Initializes the filter and fetches fetches filtered leave requests
     * @param {Object} e - Event object
     * @param {Object} filtersData - Filters
     * @return {Promise}
     */
    function initializeFilters (e, filtersData) {
      filters = filtersData;

      return fetchCount();
    }

    /**
     * Initializes the event listeners
     */
    function initializeListeners () {
      $rootScope.$on('ManagerNotification:: Initialize Filters::' + vm.eventName, initializeFilters);
      pubSub.subscribe('LeaveRequest::' + vm.eventName, fetchCount);
    }
  }
});
