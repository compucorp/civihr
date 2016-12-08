define([
  'leave-absences/shared/apis/entitlement-api',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/instances/entitlement-instance',
  'mocks/apis/entitlement-api-mock',
], function () {
  'use strict'

  describe('Entitlement', function () {
    var $provide, Entitlement, EntitlementInstance, EntitlementAPI, $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_EntitlementAPIMock_) {
      $provide.value('EntitlementAPI', _EntitlementAPIMock_);
    }));

    beforeEach(inject(function (_Entitlement_, _EntitlementInstance_, _EntitlementAPI_, _$rootScope_) {
      Entitlement = _Entitlement_;
      EntitlementInstance = _EntitlementInstance_;
      EntitlementAPI = _EntitlementAPI_;
      $rootScope = _$rootScope_;

      spyOn(EntitlementAPI, 'all').and.callThrough();
      spyOn(EntitlementAPI, 'breakdown').and.callThrough();
    }));

    afterEach(function () {
      //to excute the promise force an digest
      $rootScope.$apply();
    });

    it('has expected interface', function () {
      expect(Object.keys(Entitlement)).toEqual(['all', 'breakdown']);
    });

    it('all() are of model instances', function () {
      Entitlement.all().then(function (response) {
        expect(EntitlementAPI.all).toHaveBeenCalled();
        expect(response.every(function (modelInstance) {
          return 'defaultCustomData' in modelInstance;
        })).toBe(true);
      });
    });

    it('breakdown() are of model instances', function () {
      Entitlement.breakdown().then(function (response) {
        expect(EntitlementAPI.breakdown).toHaveBeenCalled();
        expect(response.every(function (modelInstance) {
          return 'defaultCustomData' in modelInstance;
        })).toBe(true);
      });
    });

    describe('for existing entitlements', function () {
      var existingEntitlementsPromise;

      beforeEach(function () {
        existingEntitlementsPromise = Entitlement.all();
      });

      it('has breakdown model instance', function () {
        existingEntitlementsPromise.then(function (existingEntitlements) {
          Entitlement.breakdown(existingEntitlements).then(function (response) {
            expect(response.every(function (modelInstance) {
              return 'defaultCustomData' in modelInstance;
            })).toBe(true);
          });
        });
      });

      it('has breakdown key in model', function () {
        existingEntitlementsPromise.then(function (existingEntitlements) {
          Entitlement.breakdown(existingEntitlements).then(function (response) {
            expect(response.every(function (modelInstance) {
              return 'breakdown' in modelInstance;
            })).toBe(true);
          });
        });
      });

    });
  });
});
