/* eslint-env amd, jasmine */

define([
  'mocks/data/entitlement-data',
  'mocks/data/leave-balance-report.data',
  'leave-absences/shared/apis/entitlement.api'
], function (mockData, leaveBalanceReportMockData) {
  'use strict';

  describe('EntitlementAPI', function () {
    var EntitlementAPI, $httpBackend, $rootScope;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_EntitlementAPI_, _$httpBackend_, _$rootScope_) {
      EntitlementAPI = _EntitlementAPI_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      // when the URL has this pattern
      // GET /civicrm/ajax/rest?action=get&entity=LeavePeriodEntitlement&json={"sequential":1,"api.LeavePeriodEntitlement.getremainder":{"entitlement_id":"$value.id","include_future":true}}&sequential=1
      $httpBackend.whenGET(/action=get&entity=LeavePeriodEntitlement/)
        .respond(function (method, url, data, headers, params) {
          var jsonFromParams = JSON.parse(params.json);
          // intercept same get call when chaining with withremainder call
          if ('api.LeavePeriodEntitlement.getremainder' in jsonFromParams) {
            return [200, mockData.all(true)];
          }
          return [200, mockData.all()];
        });

      /// civicrm/ajax/rest?action=getbreakdown&entity=LeavePeriodEntitlement&json={}&sequential=1
      $httpBackend.whenGET(/action=getbreakdown&entity=LeavePeriodEntitlement/)
        .respond(mockData.breakdown());

      $httpBackend.whenGET(/action=getLeaveBalances&entity=LeavePeriodEntitlement/)
        .respond(leaveBalanceReportMockData.all());
    }));

    it('has expected end points', function () {
      expect(Object.keys(EntitlementAPI)).toEqual(['all', 'breakdown', 'getLeaveBalances']);
    });

    describe('all()', function () {
      var totalEntitlements, entitlementPromise, remainderPromise;

      beforeEach(function () {
        spyOn(EntitlementAPI, 'sendGET').and.callThrough();

        totalEntitlements = mockData.all().values.length;
        entitlementPromise = EntitlementAPI.all({});
        remainderPromise = EntitlementAPI.all({}, true);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('contains expected entitlement data', function () {
        entitlementPromise.then(function (response) {
          expect(response.length).toEqual(totalEntitlements);
          var entitlementFirst = response[0];

          ['id', 'period_id', 'type_id', 'contact_id', 'overridden'].forEach(function (property) {
            expect(entitlementFirst[property]).toBeDefined();
          });
        });
      });

      it('with remainder key available', function () {
        remainderPromise.then(function (allEntitlements) {
          var entitlementFirst = allEntitlements[0];

          expect(entitlementFirst.remainder).toBeDefined();
        });
      });

      it('with remainder containing current details', function () {
        remainderPromise.then(function (allEntitlements) {
          var entitlementFirst = allEntitlements[0];
          var remainder = entitlementFirst.remainder;

          expect(Object.keys(remainder)).toContain('current');
          expect(remainder.current).toEqual(jasmine.any(Number));
        });
      });

      it('with remainder containing future details', function () {
        remainderPromise.then(function (allEntitlements) {
          var entitlementFirst = allEntitlements[0];
          var remainder = entitlementFirst.remainder;

          expect(Object.keys(remainder)).toContain('future');
          expect(remainder.future).toEqual(jasmine.any(Number));
        });
      });

      describe('entitlement value', function () {
        it('chains a call to the getentitlement endpoint', function () {
          var params = EntitlementAPI.sendGET.calls.mostRecent().args[2];

          expect(params).toEqual(jasmine.objectContaining({
            'api.LeavePeriodEntitlement.getentitlement': {
              'entitlement_id': '$value.id'
            }
          }));
        });

        it('stores the value in a `value` property', function () {
          entitlementPromise.then(function (entitlements) {
            entitlements.forEach(function (entitlement) {
              expect(entitlement.value).toBeDefined();
            });
          });
        });
      });
    });

    describe('breakdown()', function () {
      var breakdownPromise;

      beforeEach(function () {
        breakdownPromise = EntitlementAPI.breakdown();
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('returns the breakdown for all entitlements', function () {
        breakdownPromise.then(function (breakdowns) {
          expect(breakdowns.length).toEqual(mockData.breakdown().values.length);
        });
      });

      it('contains breakdown data', function () {
        breakdownPromise.then(function (allBreakdowns) {
          var breakdownsFirst = allBreakdowns[0];

          expect(breakdownsFirst.breakdown).toBeDefined();
        });
      });

      it('breakdown has expected data', function () {
        breakdownPromise.then(function (allBreakdowns) {
          var breakdownsFirst = allBreakdowns[0];
          var breakdown = breakdownsFirst.breakdown[0];

          expect(Object.keys(breakdown)).toEqual(['amount', 'expiry_date', 'type']);
        });
      });

      it('breakdown data is populated', function () {
        breakdownPromise.then(function (allBreakdowns) {
          var breakdownsFirst = allBreakdowns[0];
          var breakdown = breakdownsFirst.breakdown[0];

          expect(breakdown).toEqual(jasmine.objectContaining({
            'amount': jasmine.any(String)
          }));
        });
      });
    });

    describe('getLeaveBalances()', function () {
      beforeEach(function () {
        spyOn(EntitlementAPI, 'getAll').and.callThrough();
        EntitlementAPI.getLeaveBalances({});
        $rootScope.$apply();
      });

      it('calls the getLeaveBalances() method of LeavePeriodEntitlement entity', function () {
        expect(EntitlementAPI.getAll).toHaveBeenCalledWith(
          'LeavePeriodEntitlement', jasmine.any(Object), undefined, undefined,
          undefined, 'getLeaveBalances', undefined
        );
      });
    });
  });
});
