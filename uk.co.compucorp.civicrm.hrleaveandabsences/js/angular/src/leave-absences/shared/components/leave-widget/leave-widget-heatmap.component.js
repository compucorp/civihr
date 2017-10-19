/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/models/leave-request.model'
], function (_, moment, components) {
  components.component('leaveWidgetHeatmap', {
    bindings: {
      title: '@',
      absenceTypes: '<',
      contactId: '<',
      currentAbsencePeriod: '<'
    },
    controller: leaveWidgetHeatmapController,
    controllerAs: 'leaveWidgetHeatmap',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-widget/leave-widget-heatmap.html';
    }]
  });

  leaveWidgetHeatmapController.$include = ['$scope', 'LeaveRequest',
    'OptionGroup'];

  function leaveWidgetHeatmapController ($scope, LeaveRequest, OptionGroup) {
    var allowedLeaveStatuses = ['approved', 'admin_approved',
      'awaiting_approval', 'more_information_required'];
    var statusIds = [];
    var vm = this;

    vm.weekHeatMap = {};

    vm.$onChanges = $onChanges;

    /**
     * Initializes the controller by emiting a child is loading event.
     */
    (function init () {
      $scope.$emit('LeaveWidget::childIsLoading');
    })();

    /**
     * Implements the $onChanges method for angular controllers. When bindings
     * are ready for use, it loads all dependencies.
     */
    function $onChanges () {
      if (areBindingsReady()) {
        loadDependencies();
      }
    }

    /**
     * Returns true when the bindings are ready to be used.
     *
     * @return {Boolean}
     */
    function areBindingsReady () {
      return vm.absenceTypes && vm.contactId && vm.currentAbsencePeriod;
    }

    /**
     * Loads the status ids for absence types and leave requests for the contact.
     * After loading the dependencies, it emits a child is ready event.
     *
     * @return {Promise}
     */
    function loadDependencies () {
      return loadAbsenceTypeStatusIds()
        .then(loadLeaveRequests)
        .finally(function () {
          $scope.$emit('LeaveWidget::childIsReady');
        });
    }

    /**
     * Loads all leave requests for the contact, in the current period, of the
     * allowed statuses, and of the specific absence types.
     *
     * @return {Promise}
     */
    function loadLeaveRequests () {
      var absenceTypeIds = vm.absenceTypes.map(function (absenceType) {
        return absenceType.id;
      });

      return LeaveRequest.all({
        contact_id: vm.contactId,
        from_date: { '>=': vm.currentAbsencePeriod.start_date },
        to_date: { '<=': vm.currentAbsencePeriod.end_date },
        status_id: { IN: statusIds },
        type_id: { IN: absenceTypeIds }
      })
      .then(function (response) {
        var requests = response.list;

        mapAbsenceTypesBalance(requests);
        mapRequestsToWeekHeatMap(requests);
      });
    }

    /**
     * Loads the status ID for absence types and stores only the allowed ones.
     *
     * @return {Promise}
     */
    function loadAbsenceTypeStatusIds () {
      return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          statusIds = statuses.filter(function (status) {
            return _.includes(allowedLeaveStatuses, status.name);
          })
          .map(function (status) {
            return status.value;
          });
        });
    }

    /**
     * Finds and stores the balance for each absence type.
     *
     * @param {LeaveRequestInstance[]} requests - an array of leave requests.
     */
    function mapAbsenceTypesBalance (requests) {
      vm.absenceTypes = vm.absenceTypes.map(function (absenceType) {
        absenceType = _.assign({}, absenceType);
        absenceType.balance = requests.filter(function (request) {
          return +request.type_id === +absenceType.id;
        })
        .reduce(function (balance, request) {
          return Math.abs(balance + request.balance_change);
        }, 0);

        return absenceType;
      });
    }

    /**
     * Stores the total leave balance for each day of the week.
     *
     * @param {LeaveRequestInstance[]} requests - an array of leave requests.
     */
    function mapRequestsToWeekHeatMap (requests) {
      vm.weekHeatMap = {};

      requests.reduce(function (dates, request) {
        return dates.concat(request.dates);
      }, [])
      .forEach(function (date) {
        var dayOfTheWeek = moment(date.date).day();

        if (!vm.weekHeatMap[dayOfTheWeek]) {
          vm.weekHeatMap[dayOfTheWeek] = 0;
        }

        vm.weekHeatMap[dayOfTheWeek]++;
      });
    }
  }
});
