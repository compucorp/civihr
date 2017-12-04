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
    var entitlements;
    var vm = this;

    vm.leaveEntitlements = [];

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
        loadDependencies().then(function () {
          vm.leaveEntitlements = Entitlement.getLeaveEntitlements(
            vm.absenceTypes, entitlements);
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
     * Loads all the component dependencies (entitlements in this case) and
     * emits a child is ready event.
     *
     * @return {Promise} - Returns an empty promise when all dependencies have
     * loaded.
     */
    function loadDependencies () {
      return loadEntitlements().then(function () {
        $scope.$emit('LeaveWidget::childIsReady', childComponentName);
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
      }, true)
      .then(function (_entitlements_) {
        entitlements = _entitlements_;
      });
    }
  }
});
