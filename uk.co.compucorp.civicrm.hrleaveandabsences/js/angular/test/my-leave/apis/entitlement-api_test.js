define([
  'leave-absences/shared/modules/apis',
  'leave-absences/shared/apis/entitlement-api',
], function() {
  'use strict';

  describe('EntitlementAPI', function (){
    var entitlementAPI;

    beforeEach(module('leave-absences.apis'));

    beforeEach( inject( function(_EntitlementAPI_) {
      entitlementAPI = _EntitlementAPI_;
    }));

    it('has expected end points', function() {
      expect(Object.keys(entitlementAPI)).toEqual(['all', 'breakdown']);
    });

    describe('gets all entitlements', function() {
      it('response contains period id', function() {
        entitlementAPI.all().then(function(response) {
          expect(response).toEqual(jasmine.objectContaining({
            "period_id": "1"
          }));
        });
      });

      it('gets all entitlements with remainder/balance', function() {
        var all_entitlements =
        entitlementAPI.all("", true).then(function(all_entitlements) {
          expect(all_entitlements).toEqual(jasmine.objectContaining({
            'remainder': {
              'current': 30,
              'future': 17
            }
          }));
        });
      });
    });

    it('gets breakdown of entitlements', function() {
      entitlementAPI.breakdown().then(function(all_entitlements) {
        expect(all_entitlements).toEqual(jasmine.objectContaining({
          'remainder': {
            'current': 30,
            'future': 17
          }
        }));
      });

    });
  });

});
