/* eslint-env amd, jasmine */

define([
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
], function (moment, angular) {
  'use strict';

  describe('absenceTabEntitlements', function () {
    var $compile, $log, $rootScope, $q, component, controller,
      Contract, ContractAPIMock, Contact, ContactAPIMock,
      AbsenceType, AbsenceTypeAPIMock, AbsencePeriod, AbsencePeriodAPIMock,
      Entitlement, EntitlementAPIMock;

    beforeEach(module('leave-absences.templates', 'absence-tab',
                      'common.mocks', 'leave-absences.mocks'
    ));

    beforeEach(inject(['api.contract.mock', 'api.contact.mock', 'AbsenceTypeAPIMock', 'AbsencePeriodAPIMock', 'EntitlementAPIMock',
      function (_ContractAPIMock_, _ContactAPIMock_, _AbsenceTypeAPIMock_, _AbsencePeriodAPIMock_, _EntitlementAPIMock_) {
        ContractAPIMock = _ContractAPIMock_;
        ContactAPIMock = _ContactAPIMock_;
        AbsenceTypeAPIMock = _AbsenceTypeAPIMock_;
        AbsencePeriodAPIMock = _AbsencePeriodAPIMock_;
        EntitlementAPIMock = _EntitlementAPIMock_;
      }]));

    beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$q_, _Contract_, _Contact_, _AbsenceType_, _AbsencePeriod_, _Entitlement_) {
      $compile = _$compile_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      $q = _$q_;

      Contact = _Contact_;
      Contract = _Contract_;
      AbsenceType = _AbsenceType_;
      AbsencePeriod = _AbsencePeriod_;
      Entitlement = _Entitlement_;

      spyOn($log, 'debug');
      /*
       * @TODO the way Contact model is constructed now it is not possible to use
       * the same syntax as used for other spies as the scope for Contact will be changed
       * and "this.mockedContracts" will not work
       */
      spyOn(Contact, 'all').and.callFake(function () {
        return $q.resolve(ContactAPIMock.all());
      });
      spyOn(Contract, 'all').and.callFake(ContractAPIMock.all);
      spyOn(AbsenceType, 'all').and.callFake(AbsenceTypeAPIMock.all);
      spyOn(AbsencePeriod, 'all').and.callFake(AbsencePeriodAPIMock.all);
      spyOn(Entitlement, 'all').and.callFake(EntitlementAPIMock.all);

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
