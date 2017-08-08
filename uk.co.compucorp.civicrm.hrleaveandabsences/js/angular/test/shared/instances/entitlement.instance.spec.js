/* eslint-env amd, jasmine */

define([
  'mocks/apis/entitlement-api-mock',
  'leave-absences/shared/instances/entitlement.instance'
], function () {
  'use strict';

  describe('EntitlementInstance', function () {
    var $provide, EntitlementInstance, $rootScope;

    beforeEach(module('leave-absences.models.instances', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_EntitlementAPIMock_) {
      // EntitlementAPI is internally used by Model and hence need to be mocked
      $provide.value('EntitlementAPI', _EntitlementAPIMock_);
    }));

    beforeEach(inject(function (_EntitlementInstance_, _$rootScope_) {
      EntitlementInstance = _EntitlementInstance_;
      $rootScope = _$rootScope_;

      spyOn(EntitlementInstance, 'getBreakdown').and.callThrough();
    }));

    afterEach(function () {
      // to excute the promise force an digest
      $rootScope.$apply();
    });

    describe('defaultCustomData()', function () {
      it('has default values', function () {
        var expectedDefault = {
          remainder: {
            current: 0,
            future: 0
          },
          breakdown: []
        };
        expect(EntitlementInstance.defaultCustomData()).toEqual(expectedDefault);
      });
    });

    describe('getBreakdown()', function () {
      var entitlementInstance;

      beforeEach(function () {
        var entitlementAttributes = {
          'id': '1',
          'period_id': '1',
          'type_id': '1',
          'contact_id': '202',
          'overridden': '0'
        };
        entitlementInstance = EntitlementInstance.init(entitlementAttributes, true);
      });

      it('breakdown is empty before call', function () {
        expect(entitlementInstance.breakdown).toEqual([]);
      });

      it('makes the call', function () {
        entitlementInstance.getBreakdown().then(function () {
          expect(entitlementInstance.getBreakdown).toHaveBeenCalled();
        });
      });

      it('breakdown is populated post call', function () {
        entitlementInstance.getBreakdown().then(function () {
          expect(entitlementInstance.breakdown).not.toEqual([]);
        });
      });
    });
  });
});
