/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/models/leave-request.model'
], function (_, moment, components) {
  components.component('leaveWidgetNextLeave', {
    bindings: {
      contactId: '<',
      leaveRequestStatuses: '<'
    },
    controller: nextLeaveController,
    controllerAs: 'nextLeave',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-widget/leave-widget-next-leave.html';
    }]
  });

  nextLeaveController.$inject = ['$scope', 'LeaveRequest', 'OptionGroup',
    'shared-settings'];

  function nextLeaveController ($scope, LeaveRequest, OptionGroup,
  sharedSettings) {
    var childComponentName = 'leave-widget-next-leave';
    var dayTypes = [];
    var vm = this;
    var statusColoursMap = {
      'admin_approved': 'success',
      'approved': 'success',
      'awaiting_approval': 'warning',
      'more_information_required': 'primary'
    };

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
      if (bindingsAreReady()) {
        loadNextLeaveRequest().finally(function () {
          $scope.$emit('LeaveWidget::childIsReady', childComponentName);
        });
      }
    }

    /**
     * Returns true when contact id and leave request statuses bindings are
     * ready.
     *
     * @return {Boolean}
     */
    function bindingsAreReady () {
      return vm.contactId && vm.leaveRequestStatuses &&
        vm.leaveRequestStatuses.length;
    }

    /**
     * Returns the label for the day type id provided.
     *
     * @param {String} dayTypeId - the id for the day type.
     * @return {String}
     */
    function getDayTypeLabel (dayTypeId) {
      var dayType = _.find(dayTypes, function (dayType) {
        return +dayType.value === +dayTypeId;
      });

      return dayType.label;
    }

    /**
     * Returns the status for the next leave request.
     *
     * @return {Object}
     */
    function getNextLeaveRequestStatus () {
      return _.find(vm.leaveRequestStatuses, function (status) {
        return +status.value === +vm.nextLeaveRequest.status_id;
      });
    }

    /**
     * Returns a list of status ids.
     *
     * @return {Array}
     */
    function getStatusIds () {
      return vm.leaveRequestStatuses.map(function (status) {
        return status.value;
      });
    }

    /**
     * Loads and stores all the possible day types for leave requests.
     *
     * @return {Promise}
     */
    function loadDayTypes () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_day_type')
        .then(function (_dayTypes_) {
          dayTypes = _dayTypes_;
        });
    }

    /**
     * Loads the next leave request for the contact that has been approved or
     * is under review.
     *
     * @return {Promise}
     */
    function loadNextLeaveRequest () {
      var statusIds = getStatusIds();

      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { '>=': moment().format(sharedSettings.serverDateFormat) },
        status_id: { IN: statusIds },
        options: { limit: 1, sort: 'from_date DESC' }
      })
      .then(function (response) {
        vm.nextLeaveRequest = response.list[0];
      })
      .then(mapDateTypeLabels)
      .then(updateBalanceDeduction)
      .then(updateRequestStatus);
    }

    /**
     * Maps the from and to date type labels for the next leave requests.
     *
     * @return {Promise}
     */
    function mapDateTypeLabels () {
      return loadDayTypes().then(function () {
        vm.nextLeaveRequest = _.assign({
          from_date_type_label: getDayTypeLabel(vm.nextLeaveRequest.from_date_type),
          to_date_type_label: getDayTypeLabel(vm.nextLeaveRequest.to_date_type)
        }, vm.nextLeaveRequest);
      });
    }

    /**
     * Updates the balance deduction using the leave request balance change.
     * Math.abs is used because the balance change is negative.
     */
    function updateBalanceDeduction () {
      vm.balanceDeduction = Math.abs(vm.nextLeaveRequest.balance_change);
    }

    function updateRequestStatus () {
      var status = getNextLeaveRequestStatus();

      vm.requestStatus = {
        label: status.label,
        textColor: statusColoursMap[status.name]
      };
    }
  }
});
