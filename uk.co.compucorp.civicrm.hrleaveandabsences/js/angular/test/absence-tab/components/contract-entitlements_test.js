/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'common/angular',
  'common/angularMocks',
  'leave-absences/shared/config',
  'leave-absences/absence-tab/app',
  'common/mocks/services/api/contract-mock',
  'mocks/apis/absence-type-api-mock',
  'common/mocks/services/hr-settings-mock'
], function (_, moment, angular) {
  'use strict';

  describe('contactEntitlements', function () {
    var $compile, $log, $rootScope, controller, Contract,
      ContractAPIMock, AbsenceType, AbsenceTypeAPIMock, HRSettingsMock;

    beforeEach(module('leave-absences.templates', 'absence-tab',
                      'common.mocks', 'leave-absences.mocks'
    ));

    beforeEach(inject(['api.contract.mock', 'AbsenceTypeAPIMock', 'HR_settings', function (_ContractAPIMock_, _AbsenceTypeAPIMock_, _HRSettingsMock_) {
      ContractAPIMock = _ContractAPIMock_;
      AbsenceTypeAPIMock = _AbsenceTypeAPIMock_;
      HRSettingsMock = _HRSettingsMock_;
    }]));

    beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _Contract_, _AbsenceType_) {
      $compile = _$compile_;
      $log = _$log_;
      $rootScope = _$rootScope_;

      Contract = _Contract_;
      AbsenceType = _AbsenceType_;

      spyOn($log, 'debug');
      spyOn(AbsenceType, 'all').and.callFake(AbsenceTypeAPIMock.all);
      spyOn(Contract, 'all').and.callFake(ContractAPIMock.all);

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    it('has a contact to load for', function () {
      expect(controller.contactId).toBeDefined();
    });

    it('has loaded contracts', function () {
      expect(controller.contracts).toEqual(jasmine.any(Array));
    });

    describe('contract', function () {
      var contract, mockedContract;

      beforeEach(function () {
        contract = controller.contracts[0];
        mockedContract = ContractAPIMock.mockedContracts()[0].info.details;
      });

      it('has position', function () {
        expect(contract.position).toEqual(mockedContract.position);
      });

      it('has start date', function () {
        expect(contract.start_date).toEqual(formatDate(mockedContract.period_start_date));
      });

      it('has end date', function () {
        expect(contract.end_date).toEqual(formatDate(mockedContract.period_end_date));
      });

      it('has absences', function () {
        expect(contract.absences).toEqual(jasmine.any(Array));
      });

      describe('absence', function () {
        var absence, mockedAbsence;

        beforeEach(function () {
          absence = contract.absences[0];
          mockedAbsence = ContractAPIMock.mockedContracts()[0].info.leave[0];
        });

        it('has amount', function () {
          expect(absence.amount).toEqual(mockedAbsence.leave_amount);
        });
      });
    });

    /**
     * Compiles the controller
     */
    function compileComponent () {
      var $scope = $rootScope.$new();
      var contactId = '202';
      var component = angular.element('<contract-entitlements contact-id="' + contactId + '"></contract-entitlements>');

      $compile(component)($scope);
      $scope.$digest();

      controller = component.controller('contractEntitlements');
    }

    /**
     * Formats the date according to user settings
     *
     * @param {object} date
     * @return {string}
     */
    function formatDate (date) {
      var dateFormat = HRSettingsMock.DATE_FORMAT.toUpperCase();

      return date ? moment(date).format(dateFormat) : '';
    }
  });
});
