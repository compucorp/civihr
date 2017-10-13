/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/models/entitlement.model',
  'mocks/apis/entitlement-api-mock'
], function () {
  'use strict';

  describe('Entitlement', function () {
    var $provide, Entitlement, EntitlementAPI, $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_EntitlementAPIMock_) {
      $provide.value('EntitlementAPI', _EntitlementAPIMock_);
    }));

    beforeEach(inject(function (_Entitlement_, _EntitlementAPI_, _$rootScope_) {
      Entitlement = _Entitlement_;
      EntitlementAPI = _EntitlementAPI_;
      $rootScope = _$rootScope_;

      spyOn(EntitlementAPI, 'all').and.callThrough();
      spyOn(EntitlementAPI, 'breakdown').and.callThrough();
    }));

    afterEach(function () {
      // to excute the promise force an digest
      $rootScope.$apply();
    });

    it('has expected interface', function () {
      expect(Object.keys(Entitlement)).toEqual(['all', 'breakdown']);
    });

    describe('all()', function () {
      var entitlementPromise;

      beforeEach(function () {
        entitlementPromise = Entitlement.all();
      });

      it('calls equivalent API method', function () {
        entitlementPromise.then(function (response) {
          expect(EntitlementAPI.all).toHaveBeenCalled();
        });
      });

      it('returns model instances', function () {
        entitlementPromise.then(function (response) {
          expect(response.every(function (modelInstance) {
            return 'defaultCustomData' in modelInstance;
          })).toBe(true);
        });
      });
    });

    describe('breakdown()', function () {
      var entitlementBreakdownPromise;

      beforeEach(function () {
        entitlementBreakdownPromise = Entitlement.breakdown();
      });

      it('calls equivalent API method', function () {
        entitlementBreakdownPromise.then(function (response) {
          expect(EntitlementAPI.breakdown).toHaveBeenCalled();
        });
      });

      it('returns model instances', function () {
        entitlementBreakdownPromise.then(function (response) {
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

        it('has breakdown details', function () {
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
});
