/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/models/leave-balance-report.model',
  'common/services/notification.service'
], function (angular, _, components) {
  components.component('leaveBalanceTab', {
    controller: LeaveBalanceTabcontroller,
    controllerAs: 'leaveBalanceTab',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-balance-tab.html';
    }]
  });

  LeaveBalanceTabcontroller.$inject = ['$q', 'AbsenceType', 'LeaveBalanceReport', 'notificationService', 'Session'];

  function LeaveBalanceTabcontroller ($q, AbsenceType, LeaveBalanceReport, notification, Session) {
    var loggedInContactId;
    var pageSize = 50;
    var vm = this;

    vm.absenceTypes = [];
    vm.loading = { report: true };
    vm.report = [];
    vm.reportCount = 0;

    /**
     * Initializes the component. Loads logged in contact information and
     * the first page of the report.
     */
    (function init () {
      vm.loading.report = true;
      var firstPage = 1;

      $q.all([
        loadAbsenceTypes(),
        loadLoggedInContactId()
      ])
      .then(loadReportPage.bind(this, firstPage))
      .catch(function (error) {
        notification.error('Error', error);
      });
    })();

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
        { managed_by: loggedInContactId },
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
  }
});
