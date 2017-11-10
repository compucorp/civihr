/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/models/leave-request.model'
], function (_, moment, components) {
  components.component('leaveWidgetNextLeave', {
    bindings: {
      absenceTypes: '<',
      contactId: '<',
      leaveRequestStatuses: '<'
    },
    controller: nextLeaveController,
    controllerAs: 'nextLeave',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-widget/leave-widget-next-leave.html';
    }]
  });

  nextLeaveController.$inject = ['$q', '$scope', 'LeaveRequest', 'OptionGroup',
    'shared-settings'];

  function nextLeaveController ($q, $scope, LeaveRequest, OptionGroup,
  sharedSettings) {
    var childComponentName = 'leave-widget-next-leave';
    var vm = this;

    vm.dayTypes = {};
    vm.balanceDeduction = 0;
    vm.nextLeaveRequest = null;
    vm.requestStatus = {};

    vm.$onChanges = $onChanges;

    /**
     * Initializes the controller by emiting a child is loading event.
     */
    (function init () {
      $scope.$emit('LeaveWidget::childIsLoading', childComponentName);
    })();

    /**
     * Implements $onChanges method for AngularJS Components. Waits for bindings
     * to be ready, and then loads the next leave requests. When it's done, it
     * emits a child is ready event.
     */
    function $onChanges () {
      if (!bindingsAreReady()) {
        return;
      }

      $q.all([
        loadDayTypes(),
        loadNextLeaveRequest()
      ])
      .then(function () {
        if (vm.nextLeaveRequest) {
          mapAbsenceTypeToNextLeaveRequest();
          makeBalanceChangeAbsolute();
          storeStatusForNextRequest();
        }
      })
      .finally(function () {
        $scope.$emit('LeaveWidget::childIsReady', childComponentName);
      });
    }

    /**
     * Returns true when contact id and leave request statuses bindings are
     * ready.
     *
     * @return {Boolean}
     */
    function bindingsAreReady () {
      return vm.absenceTypes && vm.absenceTypes.length && vm.contactId &&
        vm.leaveRequestStatuses && vm.leaveRequestStatuses.length;
    }

    /**
     * Returns a list of of absence type ids that can be used to filter leave
     * requests by absence type.
     *
     * @return {Array}
     */
    function getAbsenceTypeIds () {
      return _.pluck(vm.absenceTypes, 'id');
    }

    /**
     * Returns a list of status ids.
     *
     * @return {Array}
     */
    function getStatusIds () {
      return _.pluck(vm.leaveRequestStatuses, 'value');
    }

    /**
     * Loads and stores all the possible day types for leave requests indexed
     * by value.
     *
     * @return {Promise}
     */
    function loadDayTypes () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
        .then(function (_dayTypes_) {
          vm.dayTypes = _.indexBy(_dayTypes_, 'value');
        });
    }

    /**
     * Loads the next leave request for the contact that has been approved or
     * is under review.
     *
     * @return {Promise}
     */
    function loadNextLeaveRequest () {
      var today = moment().format(sharedSettings.serverDateFormat);

      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { '>=': today },
        request_type: 'leave',
        status_id: { IN: getStatusIds() },
        type_id: { IN: getAbsenceTypeIds() },
        options: { limit: 1, sort: 'from_date DESC' }
      }, null, null, null, false) // No cache
      .then(function (response) {
        vm.nextLeaveRequest = response.list[0] || null;
      });
    }

    /**
     * Makes the next leave requst balance chance value absolute.
     */
    function makeBalanceChangeAbsolute () {
      vm.nextLeaveRequest.balance_change = Math.abs(
        vm.nextLeaveRequest.balance_change);
    }

    /**
     * Maps the absence type title to the next leave request.
     */
    function mapAbsenceTypeToNextLeaveRequest () {
      var absenceType = _.find(vm.absenceTypes, function (absenceType) {
        return +absenceType.id === +vm.nextLeaveRequest.type_id;
      }) || {};

      vm.nextLeaveRequest['type_id.title'] = absenceType.title;
    }

    /**
     * Finds and Stores the leave request status for the next leave request.
     */
    function storeStatusForNextRequest () {
      vm.requestStatus = _.find(vm.leaveRequestStatuses, function (status) {
        return +status.value === +vm.nextLeaveRequest.status_id;
      });
    }
  }
});
