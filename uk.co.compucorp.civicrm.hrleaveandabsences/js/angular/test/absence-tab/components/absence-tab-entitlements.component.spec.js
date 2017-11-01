/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'common/angular',
  'common/angularMocks',
  'leave-absences/shared/config',
  'leave-absences/absence-tab/app',
  'common/mocks/services/api/contact-mock',
  'common/mocks/services/api/contract-mock',
  'mocks/apis/absence-type-api-mock',
  'mocks/apis/absence-period-api-mock',
  'mocks/apis/entitlement-api-mock',
  'leave-absences/shared/modules/shared-settings'
], function (_, moment, angular) {
  'use strict';

  describe('absenceTabEntitlements', function () {
    var $componentController, $log, $provide, $rootScope, AbsenceType,
      controller, OptionGroup;

    beforeEach(module('leave-absences.templates', 'absence-tab', 'common.mocks', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (AbsenceTypeAPIMock, AbsencePeriodAPIMock, EntitlementAPIMock) {
      $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
      $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
      $provide.value('EntitlementAPI', EntitlementAPIMock);
    }));

    beforeEach(inject(['api.contract.mock', 'api.contact.mock',
      'api.optionGroup.mock', function (_ContractAPIMock_, _ContactAPIMock_,
      _OptionGroupAPIMock_) {
        $provide.value('api.contract', _ContractAPIMock_);
        $provide.value('api.contact', _ContactAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
      }]));

    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_,
    _AbsenceType_, _OptionGroup_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      OptionGroup = _OptionGroup_;

      spyOn($log, 'debug');

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    it('has a contact to load for', function () {
      expect(controller.contactId).toBeDefined();
    });

    it('sets loading component to true', function () {
      expect(controller.loading.component).toBe(true);
    });

    describe('After Loading absence types', function () {
      var expectedAbsenceTypes;

      beforeEach(function () {
        getIndexedCalculationUnits()
        .then(getExpectedAbsenceTypes)
        .then(function (absenceTypes) {
          expectedAbsenceTypes = absenceTypes;
        });
        $rootScope.$digest();
      });

      it('stores the absence types and their calculation units', function () {
        expect(controller.absenceTypes).toEqual(expectedAbsenceTypes);
      });

      it('sets loading component to false', function () {
        expect(controller.loading.component).toBe(false);
      });
    });

    /**
     * Compiles and store the absence tab entitlement controller.
     */
    function compileComponent () {
      controller = $componentController('absenceTabEntitlements', null, { contactId: '202' });
    }

    /**
     * Returns a calculation units map indexed by value.
     *
     * @return {Promise} - resolves to the calculation units map.
     */
    function getIndexedCalculationUnits () {
      return OptionGroup
      .valuesOf('hrleaveandabsences_absence_type_calculation_unit')
      .then(function (calculationUnits) {
        return _.indexBy(calculationUnits, 'value');
      });
    }

    /**
     * Returns a list of absence types with their calculation units names and
     * labels.
     *
     * @return {Promise} - resolves to a list of absence types.
     */
    function getExpectedAbsenceTypes (calculationUnits) {
      return AbsenceType.all().then(function (absenceTypes) {
        return absenceTypes.map(function (absenceType) {
          var unit = calculationUnits[absenceType.calculation_unit];

          return _.extend({
            'calculation_unit.name': unit.name,
            'calculation_unit.label': unit.label
          }, absenceType);
        });
      });
    }
  });
});
