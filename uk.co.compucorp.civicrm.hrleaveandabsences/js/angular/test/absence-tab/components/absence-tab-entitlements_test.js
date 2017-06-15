/* eslint-env amd, jasmine */
/* global CRM, inject */

(function (CRM) {
  define([
    'common/lodash',
    'common/moment',
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/absence-tab/app',
    'common/mocks/services/api/contract-mock',
    'mocks/apis/absence-type-api-mock',
    'leave-absences/shared/modules/shared-settings'
  ], function (_, moment, angular) {
    'use strict';

    describe('absenceTabEntitlements', function () {
      var $compile;
      var $log;
      var $rootScope;
      var $q;
      var component;
      var controller;
      var Contract;
      var ContractAPIMock;
      var AbsenceType;
      var AbsenceTypeAPIMock;

      beforeEach(module('leave-absences.templates', 'absence-tab',
                        'common.mocks', 'leave-absences.mocks'
      ));

      beforeEach(inject(['api.contract.mock', 'AbsenceTypeAPIMock', function (_ContractAPIMock_, _AbsenceTypeAPIMock_) {
        ContractAPIMock = _ContractAPIMock_;
        AbsenceTypeAPIMock = _AbsenceTypeAPIMock_;
      }]));

      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$q_, _Contract_, _AbsenceType_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        $q = _$q_;

        Contract = _Contract_;
        AbsenceType = _AbsenceType_;

        spyOn($log, 'debug');
        spyOn(AbsenceType, 'all').and.callFake(function () {
          return $q.resolve(AbsenceTypeAPIMock.all());
        });
        spyOn(Contract, 'all').and.callFake(function () {
          return $q.resolve(ContractAPIMock.all());
        });

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('has a contact to load for', function () {
        expect(controller.contactId).not.toBeNull();
      });

      it('has loaded contracts', function () {
        expect(controller.contracts).not.toBeNull();
        expect(controller.contracts).toEqual(jasmine.any(Array));
      });

      describe('contract', function () {
        var contract;
        beforeEach(function () {
          contract = controller.contracts[0];
        });
        it('has position', function () {
          expect(contract.position).toBeDefined();
        });

        it('has start date', function () {
          expect(contract.start_date).toBeDefined();
        });

        it('has end date', function () {
          expect(contract.end_date).toBeDefined();
        });

        it('has absences', function () {
          expect(contract.absences).toBeDefined();
          expect(controller.contracts).toEqual(jasmine.any(Array));
        });

        describe('absence', function () {
          var absence;
          beforeEach(function () {
            absence = contract.absences[0];
          });

          it('has amount', function () {
            expect(absence.amount).toBeDefined();
          });
        });
      });

      describe('contracts', function () {
        var contracts;
        var sortedContracts;
        beforeEach(function () {
          contracts = controller.contracts;
          sortedContracts = _.sortBy(contracts, function (contract) {
            return moment(contract.start_date.replace(/^(\d+)\/(\d+)\/(\d+)$/g, '$3-$2-$1'));
          });
        });
        it('are ordered correctly', function () {
          expect(contracts).toEqual(sortedContracts);
        });
      });

      function compileComponent () {
        var $scope = $rootScope.$new();
        var contactId = '202';

        component = angular.element('<absence-tab-entitlements contact-id="' + contactId + '"></absence-tab-entitlements>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('absenceTabEntitlements');
      }
    });
  });
})(CRM);
