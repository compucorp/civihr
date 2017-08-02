/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/waiting-approval-notification/modules/components'
], function (_, components) {
  components.component('waitingApprovalNotification', {
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/waiting-approval-notification.html';
    }],
    controllerAs: 'waitingApprovalNotification',
    controller: ['$log', '$q', '$rootScope', 'Session', 'OptionGroup', 'shared-settings', controller]
  });

  function controller ($log, $q, $rootScope, Session, OptionGroup, sharedSettings) {
    $log.debug('Component: waiting-approval-notification');

    var filters = {};
    var leaveRequestStatuses = [];
    var vm = this;
    vm.eventName = 'updateStatus';

    (function init () {
      $q.all([
        getManagerId(),
        getStatusId()
      ]).then(function () {
        $rootScope.$emit('WaitingApproval:: Initialize Filters::' + vm.eventName, filters);
      });
    })();

    /**
     * Get the logged in contact id and save it as manager id
     *
     * @returns {Promise}
     */
    function getManagerId () {
      return Session.get()
        .then(function (session) {
          filters.managed_by = session.contactId;
        });
    }

    /**
     * Get the status id for awaiting approval status
     *
     * @returns {Promise}
     */
    function getStatusId () {
      return loadStatuses()
        .then(function () {
          filters.status_id = _.find(leaveRequestStatuses, function (status) {
            return status.name === sharedSettings.statusNames.awaitingApproval;
          }).value;
        });
    }

    /**
     * Loads all the leave request statuses
     *
     * @returns {Promise}
     */
    function loadStatuses () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          leaveRequestStatuses = statuses;
        });
    }

    return vm;
  }
});
