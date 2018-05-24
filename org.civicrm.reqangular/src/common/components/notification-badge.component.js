/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/components',
  'common/services/api'
], function (_, components) {
  components.component('notificationBadge', {
    bindings: {
      filters: '<',
      refreshCountEventName: '<'
    },
    template: ['$templateCache', function ($templateCache) {
      return $templateCache.get('components/notification-badge.html');
    }],
    controllerAs: 'badge',
    controller: NotificationBadgeController
  });

  NotificationBadgeController.$inject = ['$log', '$q', 'api', 'pubSub'];

  function NotificationBadgeController ($log, $q, api, pubSub) {
    $log.debug('Component: notification-badge');

    var vm = this;
    vm.count = 0;

    (function init () {
      initListeners();
      fetchCount();
    })();

    /**
     * Fetch count of records which matches the filter
     *
     * @return {Promise}
     */
    function fetchCount () {
      var promises = _.map(vm.filters, function (filter) {
        return api.getAll(filter.apiName, filter.params, null, null, null, 'getFull', false);
      });

      return $q.all(promises)
        .then(function (results) {
          vm.count = _.reduce(results, function (memo, num) {
            return memo + num.total;
          }, 0);
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
