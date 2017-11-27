/* eslint-env amd, jasmine */

define([
  'mocks/data/entitlement-log-data',
  'leave-absences/shared/apis/entitlement-log.api'
], function (mockData) {
  'use strict';

  describe('EntitlementLogAPI', function () {
    var EntitlementLogAPI, $httpBackend;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_$httpBackend_, _EntitlementLogAPI_) {
      EntitlementLogAPI = _EntitlementLogAPI_;
      $httpBackend = _$httpBackend_;
    }));

    it('has expected interface', function () {
      expect(Object.keys(EntitlementLogAPI)).toContain('all');
    });

    describe('all()', function () {
      var entitlementLogs;

      beforeEach(function () {
        $httpBackend.whenGET(/action=get&entity=LeavePeriodEntitlementLog/)
          .respond(mockData.all());
      });

      beforeEach(function () {
        EntitlementLogAPI.all().then(function (_entitlementLogs_) {
          entitlementLogs = _entitlementLogs_;
        });
        $httpBackend.flush();
      });

      it('returns all entitlement logs', function () {
        expect(entitlementLogs).toEqual(mockData.all().values);
      });
    });
  });
});
