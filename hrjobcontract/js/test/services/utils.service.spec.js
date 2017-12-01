/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'job-contract/modules/job-contract.module'
], function () {
  'use strict';

  describe('utilsService', function () {
    var utilsService, apiService;

    beforeEach(module('job-contract'));
    beforeEach(inject(function (_utilsService_, _apiService_) {
      utilsService = _utilsService_;
      apiService = _apiService_;
    }));

    beforeEach(function () {
      spyOn(apiService, 'resource').and.callFake(function () { return { get: function () {} }; });
    });

    describe('getAbsenceType', function () {
      beforeEach(function () {
        utilsService.getAbsenceTypes();
      });

      it('returns the id, name, and title of the absence types', function () {
        expect(apiService.resource).toHaveBeenCalledWith('AbsenceType', 'get', { return: 'id,title,default_entitlement,add_public_holiday_to_entitlement' });
      });
    });

    describe('getHoursLocation', function () {
      beforeEach(function () {
        utilsService.getHoursLocation();
      });

      it('returns only the active hours/location entries', function () {
        expect(apiService.resource).toHaveBeenCalledWith('HRHoursLocation', 'get', { sequential: 1, is_active: 1 });
      });
    });

    describe('getPayScaleGrade', function () {
      beforeEach(function () {
        utilsService.getPayScaleGrade();
      });

      it('returns only the active pay scale entries', function () {
        expect(apiService.resource).toHaveBeenCalledWith('HRPayScale', 'get', { sequential: 1, is_active: 1 });
      });
    });

    describe('getManageEntitlementsPageURL', function () {
      it('returns an URL to the Manage Entitlements page', function () {
        var url = utilsService.getManageEntitlementsPageURL(1);
        expect(url).toContain('index.php?q=civicrm/admin/leaveandabsences/periods/manage_entitlements');
      });

      it('adds the contact ID to the cid parameter of the URL', function () {
        var contactId = 1;
        var url = utilsService.getManageEntitlementsPageURL(contactId);
        expect(url).toContain('&cid=' + contactId);
      });
    });
  });
});
