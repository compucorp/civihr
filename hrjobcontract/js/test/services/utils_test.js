define([
  'common/angular',
  'common/angularMocks',
  'job-contract/app'
], function () {
  'use strict';

  describe('UtilsService', function () {
    var UtilsService, API;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_UtilsService_, _API_) {
      UtilsService = _UtilsService_;
      API = _API_;
    }));

    beforeEach(function () {
      spyOn(API, 'resource').and.callFake(function () { return { get: function () {} } })
    });

    describe('getAbsenceType', function () {
      beforeEach(function () {
        UtilsService.getAbsenceTypes();
      });

      it("returns the id, name, and title of the absence types", function () {
        expect(API.resource).toHaveBeenCalledWith('AbsenceType', 'get', { return: 'id,title,default_entitlement,add_public_holiday_to_entitlement' });
      });
    });

    describe('getHoursLocation', function () {
      beforeEach(function () {
        UtilsService.getHoursLocation();
      });

      it("returns only the active hours/location entries", function () {
        expect(API.resource).toHaveBeenCalledWith('HRHoursLocation', 'get',  { sequential: 1, is_active: 1 });
      });
    });

    describe('getPayScaleGrade', function () {
      beforeEach(function () {
        UtilsService.getPayScaleGrade();
      });

      it("returns only the active pay scale entries", function () {
        expect(API.resource).toHaveBeenCalledWith('HRPayScale', 'get', { sequential: 1, is_active: 1 });
      });
    });

    describe('getManageEntitlementsPageURL', function() {

      it('returns an URL to the Manage Entitlements page', function() {
        var url = UtilsService.getManageEntitlementsPageURL(1);
        expect(url).toContain('index.php?q=civicrm/admin/leaveandabsences/periods/manage_entitlements');
      });

      it('adds the contact ID to the cid parameter of the URL', function () {
        var contactId = 1;
        var url = UtilsService.getManageEntitlementsPageURL(contactId);
        expect(url).toContain('&cid='+contactId);
      });
    });
  });
});
