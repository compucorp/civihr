define([
  'leave-absences/shared/modules/apis',
  'leave-absences/shared/apis/entitlement-api',  
  'leave-absences/shared/modules/models',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/models/instances/entitlement-instance',
], function() {
  'use strict'

  describe("Entitlement models tests", function() {
    var entitlementModel, entitlementInstance, entitlementAPI, $rootScope, $httpBackend;

    beforeEach(module('leave-absences.models'));
    beforeEach(inject( function(_Entitlement_, _EntitlementInstance_,
        _EntitlementAPI_, _$rootScope_, _$httpBackend_) {
      entitlementModel = _Entitlement_;
      entitlementInstance = _EntitlementInstance_;
      entitlementAPI = _EntitlementAPI_;
      $rootScope = _$rootScope_;
      $httpBackend = _$httpBackend_;
    }));

    it("has all and breakdown methods", function() {
      expect(Object.keys(entitlementModel)).toEqual(["all", "breakdown"]);
    });

    it("gets all model instances", function() {
      entitlementModel.all().then( function(response) {
        expect(entitlementAPI.all).toHaveBeenCalled();
        expect(response.every( function(modelInstance) {
          entitlementInstance.isInstance(modelInstance);
        })).toBe(true);
      });
    });

    it("gets all breakdown instances", function() {
      entitlementModel.breakdown().then( function(response) {
        expect(entitlementAPI.breakdown).toHaveBeenCalled();
        expect(response.every( function(modelInstance) {
          entitlementInstance.isInstance(modelInstance);
        })).toBe(true);
      });
    });

    describe("for existing entitlements", function() {
      var existingEntitlementsPromise;

      beforeEach( function() {
        existingEntitlementsPromise = entitlementModel.all();
      });

      it("has no breakdown data", function() {
        existingEntitlementsPromise.then(function(existingEntitlements) {
          expect(existingEntitlements.every( function( entitlement){
            //check if breakdown key is in object
            return 'breakdown' in entitlement;
          })).toBeFalsy();
        });
      });

      it("after breakdown call has breakdown model instance data", function() {
        existingEntitlementsPromise.then(function(existingEntitlements) {
          entitlementModel.breakdown(existingEntitlements).then( function(response) {
            expect(response.every( function(modelInstance) {
              return entitlementInstance.isInstance(modelInstance);
            })).toBe(true);
          });
        });
      });

      it("after breakdown call has breakdown key in model", function() {
        existingEntitlementsPromise.then(function(existingEntitlements) {
          entitlementModel.breakdown(existingEntitlements).then( function(response) {
            expect(response.every( function(modelInstance) {
              return 'breakdown' in modelInstance;
            })).toBe(true);
          });
        });
      });

    });
  });
});
