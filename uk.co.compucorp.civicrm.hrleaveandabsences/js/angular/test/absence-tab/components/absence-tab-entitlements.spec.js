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
    var $componentController, $log, $rootScope, controller, $provide;

    beforeEach(module('leave-absences.templates', 'absence-tab', 'common.mocks', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (AbsenceTypeAPIMock, AbsencePeriodAPIMock, EntitlementAPIMock) {
      $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
      $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
      $provide.value('EntitlementAPI', EntitlementAPIMock);
    }));

    beforeEach(inject(['api.contract.mock', 'api.contact.mock',
      function (_ContractAPIMock_, _ContactAPIMock_) {
        $provide.value('api.contract', _ContractAPIMock_);
        $provide.value('api.contact', _ContactAPIMock_);
      }]));

    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;

      spyOn($log, 'debug');

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    it('has a contact to load for', function () {
      expect(controller.contactId).toBeDefined();
    });

    function compileComponent () {
      controller = $componentController('absenceTabEntitlements', null, { contactId: '202' });
      $rootScope.$digest();
    }
  });
});
