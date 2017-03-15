define([
  'leave-absences/shared/models/leave-request-model',
  'mocks/apis/leave-request-api-mock',
  'common/mocks/services/file-uploader-mock',
], function () {
  'use strict';

  describe('LeaveRequest', function () {
    var $provide,
      LeaveRequest,
      LeaveRequestAPI,
      $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', 'common.mocks',
      function (_$provide_) {
        $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_, _FileUploaderMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('FileUploader', _FileUploaderMock_);
    }));

    beforeEach(inject(function (_LeaveRequest_, _LeaveRequestAPI_, _$rootScope_) {
      LeaveRequest = _LeaveRequest_;
      LeaveRequestAPI = _LeaveRequestAPI_;
      $rootScope = _$rootScope_;

      spyOn(LeaveRequestAPI, 'all').and.callThrough();
      spyOn(LeaveRequestAPI, 'balanceChangeByAbsenceType').and.callThrough();
      spyOn(LeaveRequestAPI, 'calculateBalanceChange').and.callThrough();
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
          expect(response.list.every(function (modelInstance) {
            return ('cancel' in modelInstance) && ('update' in modelInstance);
          })).toBe(true);
        });
      });
    });

    describe('all() with sickness request', function () {
      var leaveRequestPromise;

      beforeEach(function () {
        leaveRequestPromise = LeaveRequest.all({}, 'sick');
      });

      it('calls equivalent API method', function () {
        leaveRequestPromise.then(function () {
          expect(LeaveRequestAPI.all).toHaveBeenCalled();
        });
      });

      it('returns model instances', function () {
        leaveRequestPromise.then(function (response) {
          expect(response.list.every(function (modelInstance) {
            return ('cancel' in modelInstance) && ('update' in modelInstance);
          })).toBe(true);
        });
      });
    });

    describe('balanceChangeByAbsenceType()', function () {
      var leaveRequestPromise

      beforeEach(function () {
        leaveRequestPromise = LeaveRequest.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String));
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        leaveRequestPromise.then(function () {
          expect(LeaveRequestAPI.balanceChangeByAbsenceType).toHaveBeenCalled();
        });
      });
    });

    describe('calculateBalanceChange()', function () {
      var requestData, promise;

      beforeEach(function () {
        promise = LeaveRequest.calculateBalanceChange(jasmine.any(Object));
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
        });
      });
    });
  });
});
