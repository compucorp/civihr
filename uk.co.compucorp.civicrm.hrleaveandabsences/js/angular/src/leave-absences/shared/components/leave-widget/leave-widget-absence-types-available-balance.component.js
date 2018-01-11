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
      contactId: '<'
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
     * the component bindings. After bindings are ready, it loads entitlements,
     * maps absence types with their entitlements, and then filters only absence
     * types the contact is entitled to.
     *
     * When the absence types are ready for use it triggers a child is ready event.
     */
    function $onChanges () {
      if (areBindingsReady()) {
        loadEntitlements()
        .then(function (entitlements) {
          return _.indexBy(entitlements, 'type_id');
        })
        .then(mapAbsenceTypesWithTheirEntitlements)
        .then(filterAbsenceTypesThatCanBeEntitled)
        .then(function (filteredAbsenceTypes) {
          vm.absenceTypes = filteredAbsenceTypes;
        })
        .then(function () {
          $scope.$emit('LeaveWidget::childIsReady', childComponentName);
        });
      }
    }

    /**
     * Returns true if all bindings are ready and can be used by the component.
     *
     * @return {Boolean}
     */
    function areBindingsReady () {
      return vm.absenceTypes && vm.absencePeriod && vm.contactId;
    }

    /**
     * Filters absence types that an absence type can be entitled to the user
     * if the associated entitlement value is greater than zero,
     * the absence type can be negative, or it allows for accrual of
     * leave requests.
     *
     * @param {Array} absenceTypes - A list of absence types with their
     * associated entitlement.
     * @return {Array}
     */
    function filterAbsenceTypesThatCanBeEntitled (absenceTypes) {
      return absenceTypes.filter(function (absenceType) {
        var hasEntitlement = absenceType.entitlement && absenceType.entitlement.value > 0;
        var allowOveruse = absenceType.allow_overuse === '1';
        var allowAccrual = absenceType.allow_accruals_request === '1';

        return hasEntitlement || allowOveruse || allowAccrual;
      });
    }

    /**
     * Loads entitlements for the selected user and absence period.
     *
     * @return {Promise} - Returns an empty promise when all entitlements have
     * been loaded and mapped.
     */
    function loadEntitlements () {
      return Entitlement.all({
        'contact_id': vm.contactId,
        'period_id': vm.absencePeriod.id,
        'type_id.is_active': true
      }, true);
    }

    /**
     * Maps each absence type with their corresponding entitlement.
     *
     * @param {Object} entitlementsByAbsenceType - An index of entitlements
     *   indexed by their absence type.
     * @return {Array}
     */
    function mapAbsenceTypesWithTheirEntitlements (entitlementsByAbsenceType) {
      return vm.absenceTypes.map(function (absenceType) {
        return _.assign({
          entitlement: entitlementsByAbsenceType[absenceType.id]
        }, absenceType);
      });
    }
  }
});
