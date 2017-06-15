/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/absence-tab/modules/components',
  'common/models/contract'
], function (_, moment, components) {
  components.component('absenceTabEntitlements', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-entitlements.html';
    }],
    controllerAs: 'entitlements',
    controller: ['$controller', '$log', '$q', '$rootScope', 'AbsenceType', 'Contract', controller]
  });

  function controller ($controller, $log, $q, $rootScope, AbsenceType, Contract) {
    $log.debug('Component: absence-tab-entitlements');

    var vm = this;

    vm.contactId = vm.contactId || CRM.vars.leaveAndAbsences.contactId;
    vm.contracts = null;
    vm.absenceTypes = null;

    /**
     * Processes contracts from data and sets them to a controller
     *
     * @param {object} data
     */
    vm._setContractsProps = function (contracts) {
      contracts = _.sortBy(contracts, function (a, b) {
        return moment(b.period_start_date) - moment(a.period_start_date);
      });
      vm.contracts = _.sortBy(_.map(contracts, function (contract) {
        var info = contract['api.HRJobContract.getfulldetails'];
        var details = info.details;
        var absences = _.map(vm.absenceTypes, function (absenceType) {
          var leave = _.filter(info.leave, function (leave) {
            return leave.leave_type === absenceType.id;
          })[0];
          return {
            amount: leave ? leave.leave_amount : ''
          };
        });
        return {
          'position': details.position,
          '$start_date': moment(details.period_start_date),
          'start_date': moment(details.period_start_date).format('DD/MM/YYYY'),
          'end_date': details.period_end_date ? moment(details.period_end_date).format('DD/MM/YYYY') : '',
          'absences': absences
        };
      }), function (contract) {
        return contract.$start_date;
      });
    };

    /**
     * Loads contracts
     *
     * @return {Promise}
     */
    function loadContracts () {
      vm.contracts = null;

      return Contract.all({contact_id: vm.contactId})
        .then(function (data) {
          vm._setContractsProps(data);
        });
    }

    /**
     * Loads absence types
     *
     * @return {Promise}
     */
    function loadAbsenceTypes () {
      return AbsenceType.all()
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
        });
    }

    (function init () {
      return $q.all([
        loadAbsenceTypes()
      ])
      .then(function () {
        return loadContracts();
      });
    })();

    return vm;
  }
});
