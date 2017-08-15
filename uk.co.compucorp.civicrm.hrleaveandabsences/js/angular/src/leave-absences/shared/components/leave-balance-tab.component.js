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

  LeaveBalanceTabController.$inject = ['$q', 'AbsencePeriod', 'AbsenceType',
    'LeaveBalanceReport', 'notificationService', 'Session'];

  function LeaveBalanceTabController ($q, AbsencePeriod, AbsenceType, LeaveBalanceReport,
    notification, Session) {
    var filters = {};
    var loggedInContactId;
    var pageSize = 50;
    var vm = this;

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.loading = { component: true, report: true };
    vm.report = [];
    vm.reportCount = 0;

    /**
     * Initializes the component. Loads logged in contact information and
     * the first page of the report.
     */
    (function init () {
      $q.all([
        loadAbsencePeriods(),
        loadAbsenceTypes(),
        loadLoggedInContactId()
      ])
      .catch(function (error) {
        notification.error('Error', error);
      })
      .finally(function () {
        vm.loading.component = false;
      });
    })();

    /**
     * Updates the leave balance report using the filters passed on as parameters.
     * The list of absence types to display is updated and the first page of the
     * report is loaded.
     *
     * @param {Object} values - The filter values to use for updating the report.
     * @param {Object} values.absencePeriod - the absence period to filter by.
     * @param {Object} values.absenceType - the abence type to filter by.
     */
    vm.updateReportFilters = function (values) {
      filters = {
        absence_period: values.absencePeriod.id,
        absence_type: values.absenceType.id,
        managed_by: loggedInContactId
      };

      updateSelectedAbsenceTypes();
      loadReportPage(1);
    };

    /**
     * Loads the absence periods from the AbsencePeriod model.
     *
     * @return {Promise}
     */
    function loadAbsencePeriods () {
      return AbsencePeriod.all({options: { sort: 'title ASC' }})
      .then(function (response) {
        vm.absencePeriods = response;
      });
    }

    /**
     * Uses the AbsenceType model to populate a list of abesence types
     * sorted by title in an ascending order.
     */
    function loadAbsenceTypes () {
      return AbsenceType.all({options: { sort: 'title ASC' }})
      .then(function (response) {
        vm.absenceTypes = response;
      });
    }

    /**
     * Initializes the loggedInContactId using the session value.
     *
     * @return {Promise}
     */
    function loadLoggedInContactId () {
      return Session.get().then(function (value) {
        loggedInContactId = value.contact_id;
      });
    }

    /**
     * Loads a specific page of the report.
     *
     * @param {int} [pageNumber=1] - The number of the page to retrieve. Defaults to 1.
     * @return {Promise}
     */
    function loadReportPage (pageNumber) {
      pageNumber = pageNumber || 1;
      vm.loading.report = true;

      return LeaveBalanceReport.all(
        filters,
        { page: pageNumber, size: pageSize }
      ).then(function (response) {
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
     * Updates the selected absence types according to the absence type filter value.
     */
    function updateSelectedAbsenceTypes () {
      vm.selectedAbsenceTypes = vm.absenceTypes.filter(function (type) {
        return parseInt(type.id) === parseInt(filters.absence_type);
      });
    }
  }
});
