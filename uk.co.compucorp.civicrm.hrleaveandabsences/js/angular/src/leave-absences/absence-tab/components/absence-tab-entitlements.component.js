/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/absence-tab/modules/components'
], function (_, components) {
  components.component('absenceTabEntitlements', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['settings', function (settings) {
      return settings.pathTpl + 'components/absence-tab-entitlements.html';
    }],
    controllerAs: 'entitlements',
    controller: AbsenceTabEntitlementsController
  });

  AbsenceTabEntitlementsController.$inject = ['$q', '$log', 'AbsenceType'];

  function AbsenceTabEntitlementsController ($q, $log, AbsenceType) {
    $log.debug('Component: absence-tab-entitlements');

    var vm = this;

    vm.absenceTypes = [];
    vm.loading = { component: true };

    (function init () {
      loadAbsenceTypes().finally(function () {
        vm.loading.component = false;
      });
    })();

    /**
     * Loads Absence Types and their calculation units.
     */
    function loadAbsenceTypes () {
      return AbsenceType.all()
        .then(AbsenceType.loadCalculationUnits)
        .then(function (absenceTypes) {
          vm.absenceTypes = absenceTypes;
        });
    }
  }
});
