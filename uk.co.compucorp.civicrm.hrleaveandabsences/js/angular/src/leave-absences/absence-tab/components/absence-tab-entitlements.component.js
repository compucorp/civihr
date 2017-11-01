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

  AbsenceTabEntitlementsController.$inject = ['$q', '$log', 'AbsenceType',
    'OptionGroup'];

  function AbsenceTabEntitlementsController ($q, $log, AbsenceType, OptionGroup) {
    $log.debug('Component: absence-tab-entitlements');

    var calculationUnits = [];
    var vm = this;

    vm.absenceTypes = [];
    vm.loading = { component: true };

    (function init () {
      $q.all([
        loadAbsenceTypes(),
        loadCalculationUnits()
      ])
      .then(mapAbsenceTypeUnits)
      .finally(function () {
        vm.loading.component = false;
      });
    })();

    function loadAbsenceTypes () {
      return AbsenceType.all().then(function (data) {
        vm.absenceTypes = data;
      });
    }

    function loadCalculationUnits () {
      return OptionGroup
        .valuesOf('hrleaveandabsences_absence_type_calculation_unit')
        .then(function (_calculationUnits_) {
          calculationUnits = _.indexBy(_calculationUnits_, 'value');
        });
    }

    function mapAbsenceTypeUnits () {
      vm.absenceTypes.forEach(function (absenceType) {
        var unit = calculationUnits[absenceType.calculation_unit];

        absenceType['calculation_unit.name'] = unit.name;
        absenceType['calculation_unit.label'] = unit.label;
      });
    }
  }
});
