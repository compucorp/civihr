/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/models/entitlement.model'
], function (_, components) {
  components.component('leaveWidgetAbsenceTypesAvailableBalance', {
    bindings: {
      absencePeriod: '<',
      absenceTypes: '<',
      contactId: '<',
      jobContract: '<'
    },
    controller: leaveWidgetBalanceController,
    controllerAs: 'leaveWidgetBalance',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-widget/leave-widget-absence-types-available-balance.html';
    }]
  });

  leaveWidgetBalanceController.$inject = ['$scope', 'Entitlement'];

  function leaveWidgetBalanceController ($scope, Entitlement) {
    var childComponentName = 'leave-widget-absence-types-available-balance';
    var entitlements;
    var vm = this;

    vm.$onChanges = $onChanges;

    /**
     * Initializes the component by emiting a child is loading event.
     */
    (function init () {
      $scope.$emit('LeaveWidget::childIsLoading', childComponentName);
    })();

    /**
     * Implements the $onChanges controller method. It watches for changes in
     * the component bindings.
     */
    function $onChanges () {
      if (areBindingsReady()) {
        loadDependencies();
      }
    }

    /**
     * Returns true if all bindings are ready and can be used by the component.
     *
     * @return {Boolean}
     */
    function areBindingsReady () {
      return vm.absenceTypes && vm.absencePeriod && vm.contactId &&
        vm.jobContract;
    }

    /**
     * Returns a list of IDs of absence types the contact has entitlements for
     *
     * @return {Array}
     */
    function getContractEntitlementsIds () {
      return _.pluck(vm.jobContract.info.leave, 'leave_type');
    }

    /**
     * Loads all the component dependencies (entitlements in this case) and
     * emits a child is ready event.
     *
     * @return {Promise} - Returns an empty promise when all dependencies have
     * loaded.
     */
    function loadDependencies () {
      return loadEntitlements()
      .then(mapAbsenceTypesWithTheirEntitlements)
      .then(filterOutUnentitledAbsenceTypes)
      .then(function (absenceTypeEntitlements) {
        vm.absenceTypeEntitlements = absenceTypeEntitlements;
      })
      .finally(function () {
        $scope.$emit('LeaveWidget::childIsReady', childComponentName);
      });
    }

    /**
     * Loads entitlements for the selected user and absence period and maps
     * the entitlements to their corresponding absence type.
     *
     * @return {Promise} - Returns an empty promise when all entitlements have
     * been loaded and mapped.
     */
    function loadEntitlements () {
      return Entitlement.all({
        contact_id: vm.contactId,
        period_id: vm.absencePeriod.id,
        type_id: { IN: getContractEntitlementsIds() }
      }, true);
    }

    /**
     * Maps absence types with their entitlements.
     *
     * @param {Array} entitlements - a list of entitlements for the user.
     * @return {Array} - a list of entitlements and their corresponding absence
     * type.
     */
    function mapAbsenceTypesWithTheirEntitlements (entitlements) {
      return vm.absenceTypes.map(function (absenceType) {
        var entitlement = _.find(entitlements, function (entitlement) {
          return +absenceType.id === +entitlement.type_id;
        });

        return {
          absenceType: absenceType,
          entitlement: entitlement
        };
      });
    }

    /**
     * Returns only entitlements that have a corresponding absence type. This
     * helps filter out entitlements for disabled absence types.
     *
     * @param {Array} absenceTypeEntitlements - a list of absence type and
     * entitlement pairs.
     * @return {Array}
     */
    function filterOutUnentitledAbsenceTypes (absenceTypeEntitlements) {
      return absenceTypeEntitlements.filter(function (absenceTypeEntitlement) {
        return absenceTypeEntitlement.absenceType && absenceTypeEntitlement.entitlement;
      });
    }
  }
});
