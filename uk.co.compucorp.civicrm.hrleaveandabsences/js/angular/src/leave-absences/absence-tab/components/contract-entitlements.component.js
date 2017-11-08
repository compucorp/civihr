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
    controller: ['$log', '$q', 'HR_settings', 'AbsenceType', 'Contract', 'DateFormat', controller]
  });

  function controller ($log, $q, HRSettings, AbsenceType, Contract, DateFormat) {
    $log.debug('Component: contract-entitlements');

    var vm = this;

    vm.contracts = [];
    vm.loading = { contracts: true };

    (function init () {
      DateFormat.getDateFormat()
      .then(loadContracts)
      .finally(function () {
        vm.loading.contracts = false;
      });
    })();

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
        .then(function (data) {
          setContractsProps(data);
        });
    }

    /**
     * Processes contracts from data and sets them to a controller
     *
     * @param {Object} contracts
     */
    function setContractsProps (contracts) {
      vm.contracts = _.sortBy(contracts, function (contract) {
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

    return vm;
  }
});
