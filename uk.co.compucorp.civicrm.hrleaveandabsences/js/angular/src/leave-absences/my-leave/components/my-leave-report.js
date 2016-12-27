define([
  'leave-absences/my-leave/modules/components',
  'common/lodash'
], function (components, _) {

  components.component('myLeaveReport', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/my-leave-report.html';
    }],
    controllerAs: 'report',
    controller: [
      '$log', '$q', 'AbsencePeriod', 'AbsenceType', 'Entitlement', 'LeaveRequest',
      controller
    ]
  });


  function controller($log, $q, AbsencePeriod, AbsenceType, Entitlement, LeaveRequest) {
    $log.debug('Component: my-leave-report');

    var vm = Object.create(this);

    vm.absencePeriods = [];
    vm.absenceTypes = [];
    vm.balanceChanges = {};
    vm.currentPeriod = null;
    vm.loading = true;
    vm.isOpen = {
      approved: false,
      entitlement: false,
      holiday: false,
      open: false,
      other: false
    };

    /**
     * Changes the current period and reload all related data
     *
     * @param  {type} newPeriod
     */
    vm.changePeriod = function (newPeriod) {
      vm.currentPeriod = newPeriod;
      vm.loading = true;

      $q.all([
        loadEntitlements(),
        loadBalanceChanges()
      ])
      .then(function () {
        vm.loading = false;
      });
    };

    init();

    /**
     * Init code
     */
    function init() {
      return $q.all([
        loadAbsenceTypes(),
        loadAbsencePeriods()
      ])
      .then(function () {
        return $q.all([
          loadEntitlements(),
          loadBalanceChanges()
        ]);
      })
      .then(function () {
        vm.loading = false;
      });
    }

    /**
     * Loads the absence periods
     *
     * @return {Promise}
     */
    function loadAbsencePeriods() {
      return AbsencePeriod.all()
        .then(function (absencePeriods) {
          vm.absencePeriods = absencePeriods;
          vm.currentPeriod = _.find(vm.absencePeriods, function (period) {
            return period.current === true;
          });
        });
    }

    /**
     * Loads the absence types
     *
     * @return {Promise}
     */
    function loadAbsenceTypes() {
      return AbsenceType.all()
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
        });
    }

    /**
     * Loads the balance changes of the various sections
     *
     * @return {Promise}
     */
    function loadBalanceChanges() {
      return $q.all([
        LeaveRequest.balanceChangeByAbsenceType({
          contact_id: vm.contactId,
          period_id: vm.currentPeriod.id,
          public_holiday: true
        }),
        LeaveRequest.balanceChangeByAbsenceType({
          contact_id: vm.contactId,
          period_id: vm.currentPeriod.id,
          statuses: [
            '<value of OptionValue "approved">'
          ]
        }),
        LeaveRequest.balanceChangeByAbsenceType({
          contact_id: vm.contactId,
          period_id: vm.currentPeriod.id,
          statuses: [
            '<value of OptionValue "awaiting approval">',
            '<value of OptionValue "more information">'
          ]
        })
      ])
      .then(function (results) {
        vm.balanceChanges.public_holidays = results[0];
        vm.balanceChanges.approved = results[1];
        vm.balanceChanges.open = results[2];
      });
    }

    /**
     * Loads all the entitlements
     *
     * @return {Promise}
     */
    function loadEntitlements() {
      return Entitlement.all({
        contact_id: vm.contactId,
        period_id: vm.currentPeriod.id
      }, true)
      .then(function (entitlements) {
        vm.entitlements = entitlements;
      });
    }

    return vm;
  }
});
