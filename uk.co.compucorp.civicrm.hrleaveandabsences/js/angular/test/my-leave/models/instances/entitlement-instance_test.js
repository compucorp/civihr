define([
  'leave-absences/shared/modules/apis',
  'leave-absences/shared/apis/entitlement-api',
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/models/instances/entitlement-instance',
], function() {
  'use strict'

  describe("EntitlementInstance", function() {
    var entitlementModel, entitlementInstance, entitlementAPI, $rootScope, $httpBackend;

    beforeEach(module('leave-absences.models'));

    beforeEach(inject( function(_Entitlement_, _EntitlementInstance_) {
      entitlementModel = _Entitlement_;
      entitlementInstance = _EntitlementInstance_;
    }));

    it("has default values", function() {
      var expectedDefault = {
        remainder: { current: 0, future: 0 },
        breakdown: []
      };
      expect(entitlementInstance.defaultCustomData()).toEqual(expectedDefault);
    });

    describe("with breakdown call", function() {
      it("will get the breakdown details for given entitlement", function(){
        entitlementModel.all().then( function(response) {
          //get one entitlement
          var entitlementInstance = response[0];
          expect(entitlementInstance.breakdown).toBeNull();
          entitlementInstance.breakdown().then( function(afterBreakdown) {
            expect(entitlementInstance.breakdown).toHaveBeenCalled();
            expect(entitlementInstance.breakdown).not.toBeNull();
          });
        });
      });
    });
  });
});
