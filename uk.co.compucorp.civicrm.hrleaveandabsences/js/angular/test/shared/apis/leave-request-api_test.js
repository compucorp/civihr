define([
  'mocks/data/leave-request-data',
  'leave-absences/shared/apis/leave-request-api',
], function (mockData) {
  'use strict';

  describe('LeaveRequestAPI', function () {
    var LeaveRequestAPI, $httpBackend, $rootScope;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_LeaveRequestAPI_, _$httpBackend_, _$rootScope_) {
      LeaveRequestAPI = _LeaveRequestAPI_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      //Intercept backend calls for LeaveRequest.all
      $httpBackend.whenGET(/action\=getFull&entity\=LeaveRequest/)
        .respond(mockData.all());

      //Intercept backend calls for LeaveRequest.balanceChangeByAbsenceType
      $httpBackend.whenGET(/action\=getbalancechangebyabsencetype&entity\=LeaveRequest/)
        .respond(mockData.balanceChangeByAbsenceType());
    }));

    describe('all()', function () {
      var leaveRequestPromise;

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'getAll').and.callThrough();
        leaveRequestPromise = LeaveRequestAPI.all();
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the getAll() method', function () {
        expect(LeaveRequestAPI.getAll).toHaveBeenCalled();
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.getAll.calls.mostRecent().args[5]).toBe('getFull');
      });

      it('returns all the data', function () {
        leaveRequestPromise.then(function (response) {
          expect(response.list).toEqual(mockData.all().values);
        });
      });
    });

    describe('balanceChangeByAbsenceType()', function () {

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
      });

      describe('error handling', function () {

        afterEach(function () {
          $rootScope.$apply();
        });

        function commonExpect(data) {
          expect(data).toEqual({
            is_error: 1,
            error_message: 'contact_id and period_id are mandatory'
          });
        }

        it('throws error if contact_id is blank', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(null, jasmine.any(String))
            .then(commonExpect);
        });

        it('throws error if periodId is blank', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), null)
            .then(commonExpect);
        });
      });

      describe('default values', function () {

        afterEach(function () {
          $httpBackend.flush();
        });

        it('status and publicHoliday has default values if falsy values has been passed', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String));

          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', {
            contact_id: jasmine.any(String),
            period_id: jasmine.any(String),
            statuses: null,
            public_holiday: false
          });
        });

        it('status and publicHoliday has original values if truthy values has been passed', function () {
          LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), true);

          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', {
            contact_id: jasmine.any(String),
            period_id: jasmine.any(String),
            statuses: jasmine.any(Array),
            public_holiday: true
          });
        });
      });

      it('contains expected data', function () {
        LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), true).then(function (response) {
          expect(response).toEqual(mockData.balanceChangeByAbsenceType().values);
        });

        $httpBackend.flush();
      });
    });
  });
});
