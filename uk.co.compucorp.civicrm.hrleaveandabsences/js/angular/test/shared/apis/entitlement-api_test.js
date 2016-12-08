define([
  'mocks/data/entitlement-data',
  'leave-absences/shared/apis/entitlement-api',
], function (mockData) {
  'use strict';

  describe('EntitlementAPI', function () {
    var EntitlementAPI, $httpBackend;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_EntitlementAPI_, _$httpBackend_) {
      EntitlementAPI = _EntitlementAPI_;
      $httpBackend = _$httpBackend_;

      //when the URL has this pattern
      //civicrm/ajax/rest?action=get&entity=LeavePeriodEntitlement
      //GET /civicrm/ajax/rest?action=get&entity=LeavePeriodEntitlement&json={"sequential":1,"api.LeavePeriodEntitlement.getremainder":{"entitlement_id":"$value.id","include_future":true}}&sequential=1
      $httpBackend.whenGET(/action\=get&entity\=LeavePeriodEntitlement/)
        .respond(function (method, url, data, headers, params) {
          if ('json' in params) {
            var jsonFromParams = JSON.parse(params.json);
            if ('api.LeavePeriodEntitlement.getremainder' in jsonFromParams) {

              return [200, mockData.all({}, true)];
            }
          }

          return [200, mockData.all()];
        });

      ///civicrm/ajax/rest?action=getbreakdown&entity=LeavePeriodEntitlement&json={}&sequential=1
      $httpBackend.whenGET(/action=getbreakdown&entity=LeavePeriodEntitlement/)
        .respond(mockData.breakdown());

    }));

    it('has expected end points', function () {
      expect(Object.keys(EntitlementAPI)).toEqual(['all', 'breakdown']);
    });

    describe('all() entitlements', function () {
      var totalEntitlements;
      beforeEach(function () {
        totalEntitlements = mockData.totalEntitlements();
      });

      it('response contains period id', function () {
        EntitlementAPI.all().then(function (response) {
          expect(response.length).toEqual(totalEntitlements);
          var entitlementFirst = response[0];
          expect(entitlementFirst).toEqual(jasmine.objectContaining({
            'period_id': '1'
          }));
        });
        $httpBackend.flush();
      });

      it('with remainder/balance', function () {
        EntitlementAPI.all({}, true).then(function (all_entitlements) {
          var entitlementFirst = all_entitlements[0];
          expect(entitlementFirst).toEqual(jasmine.objectContaining({
            'remainder': {
              'current': 11,
              'future': 5
            }
          }));
        });
        $httpBackend.flush();
      });

    });

    describe('breakdown() entitlements', function () {
      it('has remainder amounts of leave', function () {
        EntitlementAPI.breakdown().then(function (allBreakdowns) {
          var breakdownsFirst = allBreakdowns[0];
          expect('breakdown' in breakdownsFirst).toBe(true);
          var breakdown = breakdownsFirst.breakdown[0];
          expect(breakdown).toEqual(jasmine.objectContaining({
            'amount': '20.00'
          }));
          $httpBackend.flush();
        });
      });
    });
  });
});
