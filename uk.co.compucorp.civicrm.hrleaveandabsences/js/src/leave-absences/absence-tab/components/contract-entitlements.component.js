/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/absence-tab/modules/components',
  'common/models/contract'
], function (_, moment, components) {
  components.component('contractEntitlements', {
    bindings: {
      absenceTypes: '<',
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/contract-entitlements.html';
    }],
    controllerAs: 'entitlements',
    controller: contractEntitlementsController
  });

  contractEntitlementsController.$inject = ['$log', '$q', 'HR_settings',
    'AbsenceType', 'Contract', 'DateFormat'];

  function contractEntitlementsController ($log, $q, HRSettings,
    AbsenceType, Contract, DateFormat) {
    $log.debug('Component: contract-entitlements');

    var vm = this;

    vm.contracts = [];
    vm.loading = { contracts: true };

    (function init () {
      DateFormat.getDateFormat()
        .then(loadContracts)
        .then(filterAbsenceTypes)
        .then(setContractsProps)
        .finally(function () {
          vm.loading.contracts = false;
        });
    })();

    /**
     * Filters absence types basing on loaded entitlements
     */
    function filterAbsenceTypes () {
      vm.absenceTypes = _.filter(vm.absenceTypes, function (absenceType) {
        return _.find(vm.contracts, function (contract) {
          return _.find(contract.info.leave, function (leave) {
            return leave.leave_type === absenceType.id;
          });
        });
      });
    }

    /**
     * Formats the date according to user settings
     *
     * @param {Object} date
     * @return {string}
     */
    function formatDate (date) {
      var dateFormat = HRSettings.DATE_FORMAT.toUpperCase();

      return date ? moment(date).format(dateFormat) : '';
    }

    /**
     * Loads contracts
     *
     * @return {Promise}
     */
    function loadContracts () {
      return Contract.all({ contact_id: vm.contactId })
        .then(function (contracts) {
          vm.contracts = contracts;
        });
    }

    /**
     * Processes contracts from data and sets them to a controller
     */
    function setContractsProps () {
      vm.contracts = _.sortBy(vm.contracts, function (contract) {
        return moment(contract.info.details.period_start_date);
      }).map(function (contract) {
        var info = contract.info;
        var details = info.details;
        var absences = _.map(vm.absenceTypes, function (absenceType) {
          var leave = _.filter(info.leave, function (leave) {
            return leave.leave_type === absenceType.id;
          })[0];

          return {
            amount: leave ? leave.leave_amount : '',
            calculation_unit: absenceType['calculation_unit_name']
          };
        });

        return {
          'position': details.position,
          'start_date': formatDate(details.period_start_date),
          'end_date': formatDate(details.period_end_date),
          'absences': absences
        };
      });
    }
  }
});
