define([
  'leave-absences/shared/models/leave-request-model',
  'mocks/apis/leave-request-api-mock',
], function () {
  'use strict';

  describe('LeaveRequest', function () {
    var $provide,
      LeaveRequest,
      LeaveRequestAPI,
      $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
    }));

    beforeEach(inject(function (_LeaveRequest_, _LeaveRequestAPI_, _$rootScope_) {
      LeaveRequest = _LeaveRequest_;
      LeaveRequestAPI = _LeaveRequestAPI_;
      $rootScope = _$rootScope_;

      spyOn(LeaveRequestAPI, 'all').and.callThrough();
      spyOn(LeaveRequestAPI, 'balanceChangeByAbsenceType').and.callThrough();
    }));

    afterEach(function () {
      $rootScope.$apply();
    });

    describe('all()', function () {
      var leaveRequestPromise;

      beforeEach(function () {
        leaveRequestPromise = LeaveRequest.all();
      });

      it('calls equivalent API method', function () {
        leaveRequestPromise.then(function () {
          expect(LeaveRequestAPI.all).toHaveBeenCalled();
        });
      });

      it('returns model instances', function () {
        leaveRequestPromise.then(function (response) {
          expect(response.every(function (modelInstance) {
            return ('cancel' in modelInstance) && ('update' in modelInstance);
          })).toBe(true);
        });
      });
    });

    it('balanceChangeByAbsenceType() calls equivalent API method', function () {
      var leaveRequestPromise = LeaveRequest.balanceChangeByAbsenceType();

      leaveRequestPromise.then(function () {
        expect(LeaveRequestAPI.balanceChangeByAbsenceType).toHaveBeenCalled();
      });
    });
  });
});
