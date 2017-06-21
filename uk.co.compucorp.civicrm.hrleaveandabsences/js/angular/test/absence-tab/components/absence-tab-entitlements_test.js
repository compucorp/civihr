/* eslint-env amd, jasmine */

define([
  'common/moment',
  'common/angular',
  'common/angularMocks',
  'leave-absences/shared/config',
  'leave-absences/absence-tab/app',
  'common/mocks/services/api/contract-mock',
  'mocks/apis/absence-type-api-mock',
  'leave-absences/shared/modules/shared-settings'
], function (moment, angular) {
  'use strict';

  describe('absenceTabEntitlements', function () {
    var $compile, $log, $rootScope, $q, component, controller, Contract,
      ContractAPIMock, AbsenceType, AbsenceTypeAPIMock;

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
      expect(controller.contactId).toBeDefined();
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
