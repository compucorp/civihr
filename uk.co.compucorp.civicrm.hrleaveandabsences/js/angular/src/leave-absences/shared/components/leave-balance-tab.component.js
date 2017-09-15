/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/components/leave-balance-tab-filters.component',
  'leave-absences/shared/models/leave-balance-report.model',
  'common/services/notification.service'
], function (angular, _, components) {
  components.component('leaveBalanceTab', {
    controller: LeaveBalanceTabController,
    controllerAs: 'leaveBalanceTab',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-balance-tab.html';
    }]
  });

  LeaveBalanceTabController.$inject = ['$q', '$scope', '$rootScope', 'AbsencePeriod',
    'AbsenceType', 'LeaveBalanceReport', 'notificationService', 'Session',
    'shared-settings', 'checkPermissions'];

  function LeaveBalanceTabController ($q, $scope, $rootScope, AbsencePeriod,
    AbsenceType, LeaveBalanceReport, notification, Session,
    sharedSettings, checkPermissions) {
    var filters = {};
    var vm = this;

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.loading = { component: true, report: true };
    vm.loggedInContactId = null;
    vm.pagination = { page: 1, size: 50 };
    vm.report = [];
    vm.reportCount = 0;
    vm.userRole = null;

    vm.loadReportCurrentPage = loadReportCurrentPage;

    /**
     * Initializes the component. Loads dependencies needed by the component
     * and watches for events coming from child components.
     */
    (function init () {
      initWatchers();
      loadDependencies()
      .then(function () {
        vm.loading.component = false;
      });
    })();

    /**
     * Loads the absence periods from the AbsencePeriod model.
     *
     * @return {Promise}
     */
    function loadAbsencePeriods () {
      return AbsencePeriod.all({ options: { sort: 'title ASC' } })
      .then(function (response) {
        vm.absencePeriods = response;
      });
    }

    /**
     * Uses the AbsenceType model to populate a list of abesence types
     * sorted by title in an ascending order.
     */
    function loadAbsenceTypes () {
      return AbsenceType.all({ options: { sort: 'title ASC' } })
      .then(function (response) {
        vm.absenceTypes = response;
      });
    }

    /**
     * Loads all dependencies needed by the component and its children.
     *
     * @return {Promise}
     */
    function loadDependencies () {
      return $q.all([
        loadAbsencePeriods(),
        loadAbsenceTypes(),
        loadLoggedInContactId(),
        loadUserRole()
      ])
      .catch(function (error) {
        notification.error('Error', error);
      });
    }

    /**
     * Initializes the loggedInContactId using the session value.
     *
     * @return {Promise}
     */
    function loadLoggedInContactId () {
      return Session.get().then(function (value) {
        vm.loggedInContactId = value.contactId;
      });
    }

    /**
     * Loads a specific page of the report.
     *
     * @return {Promise}
     */
    function loadReportCurrentPage () {
      vm.loading.report = true;

      return LeaveBalanceReport.all(filters, vm.pagination, undefined, undefined, false)
      .then(function (response) {
        vm.report = indexLeaveBalanceAbsenceTypes(response.list);
        vm.reportCount = response.total;
      })
      .catch(function (error) {
        notification.error('Error', error.error_message);
      })
      .finally(function () {
        vm.loading.report = false;
      });
    }

    /**
     * Indexes the absence types of each leave balance so they can be
     * referenced in order on the view.
     *
     * @param {Array} - The leave balance array that contains
     * @return {Array}
     */
    function indexLeaveBalanceAbsenceTypes (leaveBalance) {
      return leaveBalance.map(function (contactLeaveBalance) {
        contactLeaveBalance = angular.copy(contactLeaveBalance);

        contactLeaveBalance.absence_types = _.indexBy(
          contactLeaveBalance.absence_types,
          function (absenceType) {
            return absenceType.id;
          }
        );

        return contactLeaveBalance;
      });
    }

    /**
     * Sets up watchers for events fired by child components.
     */
    function initWatchers () {
      $scope.$on('LeaveBalanceFilters::update', updateReportFilters);
      $rootScope.$on('LeaveRequest::new', refresh);
    }

    /**
     * Loads the user's role based on their permissions
     *
     * @return {Promise}
     */
    function loadUserRole () {
      return checkPermissions(sharedSettings.permissions.admin.administer)
        .then(function (isAdmin) {
          vm.userRole = isAdmin ? 'admin' : 'manager';
        });
    }

    /**
     * Refreshes component with current filters
     */
    function refresh (event) {
      updateReportFilters(event, filters);
    }

    /**
     * Updates the leave balance report using the filters passed on as parameters.
     * The list of absence types to display is updated and the first page of the
     * report is loaded.
     *
     * @param {Object} event - the component event handler.
     * @param {Object} _filters_ - The filter values to use for updating the report.
     * it contains the following properties:
     * - period_id - the absence period ID to filter by.
     * - type_id - the abence type ID to filter by.
     * - managed_by - the managing user ID to filter by.
     */
    function updateReportFilters (event, _filters_) {
      filters = _filters_;
      vm.pagination.page = 1;

      updateSelectedAbsenceTypes();
      vm.loadReportCurrentPage();
    }

    /**
     * Updates the selected absence types according to the absence type filter value.
     */
    function updateSelectedAbsenceTypes () {
      vm.selectedAbsenceTypes = vm.absenceTypes.filter(function (type) {
        return +type.id === +filters.type_id;
      });
    }
  }
});
