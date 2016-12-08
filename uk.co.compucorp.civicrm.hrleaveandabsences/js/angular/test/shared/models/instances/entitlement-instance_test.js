define([
  'leave-absences/shared/apis/entitlement-api',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/models/instances/entitlement-instance',
], function () {
  'use strict'

  describe('EntitlementInstance', function () {
    var Entitlement, EntitlementInstance, $rootScope, $httpBackend, EntitlementAPIMock;

    beforeEach(module('leave-absences.models'));

    beforeEach(inject(function (_Entitlement_, _EntitlementInstance_) {
      Entitlement = _Entitlement_;
      EntitlementInstance = _EntitlementInstance_;
    }));

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

    describe('breakdown()', function () {
      it('will get breakdown details for given entitlement', function () {
        Entitlement.all().then(function (response) {
          var EntitlementInstance = response[0];
          expect(EntitlementInstance.breakdown).toBeNull();
          EntitlementInstance.breakdown().then(function (afterBreakdown) {
            expect(EntitlementInstance.breakdown).toHaveBeenCalled();
            expect(EntitlementInstance.breakdown).not.toBeNull();
          });
        });
      });
    });
  });
});
