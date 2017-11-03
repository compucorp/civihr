/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/models/entitlement.model',
  'mocks/apis/entitlement-api-mock',
  'mocks/apis/entitlement-log-api-mock'
], function () {
  'use strict';

  describe('Entitlement', function () {
    var $provide, $rootScope, Entitlement, EntitlementAPI, EntitlementLogAPI;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (EntitlementAPIMock, EntitlementLogAPIMock) {
      $provide.value('EntitlementAPI', EntitlementAPIMock);
      $provide.value('EntitlementLogAPI', EntitlementLogAPIMock);
    }));

    beforeEach(inject(function (_$rootScope_, _Entitlement_, _EntitlementAPI_,
    _EntitlementLogAPI_) {
      Entitlement = _Entitlement_;
      EntitlementAPI = _EntitlementAPI_;
      EntitlementLogAPI = _EntitlementLogAPI_;
      $rootScope = _$rootScope_;

      spyOn(EntitlementAPI, 'all').and.callThrough();
      spyOn(EntitlementAPI, 'breakdown').and.callThrough();
      spyOn(EntitlementLogAPI, 'all').and.callThrough();
    }));

    afterEach(function () {
      // to excute the promise force an digest
      $rootScope.$apply();
    });

    it('has expected interface', function () {
      expect(Object.keys(Entitlement)).toEqual(['all', 'breakdown', 'logs']);
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

    describe('logs', function () {
      var entitlementLogs, spyCallArgs;
      var contactId = 202;
      var periodId = 301;

      beforeEach(function () {
        Entitlement.logs({
          contact_id: contactId,
          period_id: periodId
        })
        .then(function (_entitlementLogs_) {
          entitlementLogs = _entitlementLogs_;
        });
        $rootScope.$apply();

        spyCallArgs = EntitlementLogAPI.all.calls.argsFor(0)[0];
      });

      it('calls equivalent API method', function () {
        expect(EntitlementLogAPI.all).toHaveBeenCalled();
      });

      it('filters by contact id', function () {
        expect(spyCallArgs['entitlement_id.contact_id']).toBe(contactId);
      });

      it('filters by period id', function () {
        expect(spyCallArgs['entitlement_id.period_id']).toBe(periodId);
      });

      it('returns the entitlement fields, the editor id, comments, and created date', function () {
        expect(spyCallArgs['return']).toContain('entitlement_id');
        expect(spyCallArgs['return']).toContain('entitlement_id.type_id');
        expect(spyCallArgs['return']).toContain('entitlement_amount');
        expect(spyCallArgs['return']).toContain('editor_id');
        expect(spyCallArgs['return']).toContain('comment');
        expect(spyCallArgs['return']).toContain('created_date');
      });

      it('returns the results of the api call', function () {
        expect(entitlementLogs.length).toBeGreaterThan(0);
      });
    });
  });
});
