define([
  'common/angularMocks',
  'job-contract/app'
], function () {
  'use strict';

  describe('UtilsService', function () {
    var UtilsService;

    beforeEach(module('hrjc'));

    beforeEach(inject(function (_UtilsService_) {
      UtilsService = _UtilsService_;
    }));

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
