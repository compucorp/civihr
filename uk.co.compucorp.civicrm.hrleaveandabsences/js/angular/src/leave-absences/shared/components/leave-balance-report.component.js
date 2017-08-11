/* eslint-env amd */
/* global angular */

define([
  'common/lodash',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/models/leave-balance-report.model',
  'common/services/notification'
], function (_, components) {
  components.component('leaveBalanceReport', {
    controller: controller,
    controllerAs: 'vm',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-balance-report.html';
    }]
  });

  controller.$inject = ['$q', 'AbsenceType', 'LeaveBalanceReport', 'notification', 'Session'];

  function controller ($q, AbsenceType, LeaveBalanceReport, notification, Session) {
    var loggedInContactId;
    var pageSize = 50;
    var vm = this;

    vm.absenceTypes = [];
    vm.loading = { report: false };
    vm.report = [];
    vm.reportCount = 0;

    /**
     * Initializes the component. Loads logged in contact information and
     * the first page of the report.
     */
    vm.$onInit = function () {
      vm.loading.report = true;

      $q.all([
        initAbsenceTypes(),
        initLoggedInContactId()
      ])
      .then(function () {
        return vm.loadReportPage(1);
      })
      .catch(function (error) {
        notification.error('Error', error);
      });
    };

    /**
     * Loads a specific page of the report.
     *
     * @return {Promise}
     */
    vm.loadReportPage = function (pageNumber) {
      pageNumber = pageNumber || 1;
      vm.loading.report = true;

      return LeaveBalanceReport.all(
        { managed_by: loggedInContactId },
        { page: pageNumber, size: pageSize }
      ).then(function (response) {
        vm.report = sortBlanceReportAbsenceTypes(response.list);
        vm.reportCount = response.total;
      })
      .catch(function (error) {
        notification.error('Error', error.error_message);
      })
      .finally(function () {
        vm.loading.report = false;
      });
    };

    /**
     *
     */
    function initAbsenceTypes () {
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
    function initLoggedInContactId () {
      return Session.get().then(function (value) {
        loggedInContactId = value.contact_id;
      });
    }

    /**
     * Sorts the absence types of each balance report according to the order of
     * vm.absenceTypes records.
     *
     * @return {Object}
     */
    function sortBlanceReportAbsenceTypes (report) {
      return report.map(function (record) {
        record = angular.copy(record);

        record.absence_types = vm.absenceTypes.map(function (sortedType) {
          return _.find(record.absence_types, function (unsortedType) {
            return parseInt(sortedType.id) === parseInt(unsortedType.id);
          });
        });

        return record;
      });
    }
  }
});
