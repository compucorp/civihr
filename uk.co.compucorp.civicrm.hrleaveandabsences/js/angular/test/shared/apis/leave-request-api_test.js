define([
  'mocks/data/leave-request-data',
  'leave-absences/shared/apis/leave-request-api',
], function (mockData) {
  'use strict';

  describe('LeaveRequestAPI', function () {
    var LeaveRequestAPI, $httpBackend;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_LeaveRequestAPI_, _$httpBackend_) {
      LeaveRequestAPI = _LeaveRequestAPI_;
      $httpBackend = _$httpBackend_;

      //Intercept backend calls for LeaveRequest.all
      $httpBackend.whenGET(/action\=get&entity\=LeaveRequest/)
        .respond(function () {
          return [200, mockData.all()];
        });

      //Intercept backend calls for LeaveRequest.balanceChangeByAbsenceType
      $httpBackend.whenGET(/action\=getbalancechangebyabsencetype&entity\=LeaveRequest/)
        .respond(function () {
          return [200, mockData.balanceChangeByAbsenceType()];
        });
    }));

    it('all() contains expected data', function () {
      spyOn(LeaveRequestAPI, "getAll").and.callThrough();
      var leaveRequestPromise = LeaveRequestAPI.all("filters", "pagination", "sort", "params");

      leaveRequestPromise.then(function (response) {
        expect(response.list).toEqual(mockData.all().values);
      });

      expect(LeaveRequestAPI.getAll).toHaveBeenCalledWith('LeaveRequest', "filters", "pagination", "sort", "params", 'getFull');

      $httpBackend.flush();
    });

    describe('balanceChangeByAbsenceType()', function () {

      beforeEach(function () {
        spyOn(LeaveRequestAPI, "sendGET").and.callThrough();
      });

      it('throws error if contact_id is blank', function () {
        var balanceChangeByAbsenceTypeFn = function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(null, "periodId")
        };

        expect(balanceChangeByAbsenceTypeFn).toThrow(new Error("contact_id and period_id should have truthy value"));
      });

      it('throws error if periodId is blank', function () {
        var balanceChangeByAbsenceTypeFn = function () {
          LeaveRequestAPI.balanceChangeByAbsenceType("contactId", null)
        };

        expect(balanceChangeByAbsenceTypeFn).toThrow(new Error("contact_id and period_id should have truthy value"));
      });

      it('status and publicHoliday has default values if falsy values has been passed', function () {
        LeaveRequestAPI.balanceChangeByAbsenceType("contactId", "periodId");

        expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', {
          contact_id: "contactId",
          period_id: "periodId",
          statuses: null,
          public_holiday: false
        });
      });

      it('status and publicHoliday has original values if truthy values has been passed', function () {
        LeaveRequestAPI.balanceChangeByAbsenceType("contactId", "periodId", "statuses", true);

        expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', {
          contact_id: "contactId",
          period_id: "periodId",
          statuses: "statuses",
          public_holiday: true
        });
      });

      it('contains expected data', function () {
        LeaveRequestAPI.balanceChangeByAbsenceType("contactId", "periodId", "statuses", true).then(function (response) {
          expect(response).toEqual(mockData.balanceChangeByAbsenceType().values);
        });

        $httpBackend.flush();
      });
    });

  });
});
