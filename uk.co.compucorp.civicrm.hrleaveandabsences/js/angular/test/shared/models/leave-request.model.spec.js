/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/models/leave-request.model',
  'mocks/apis/leave-request-api-mock'
], function () {
  'use strict';

  describe('LeaveRequest', function () {
    var $provide, LeaveRequest, LeaveRequestAPI, $rootScope, OptionGroup, OptionGroupAPIMock;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
    }));

    beforeEach(inject(function (_LeaveRequest_, _LeaveRequestAPI_, _$rootScope_, _OptionGroup_, _OptionGroupAPIMock_) {
      LeaveRequest = _LeaveRequest_;
      LeaveRequestAPI = _LeaveRequestAPI_;
      $rootScope = _$rootScope_;
      OptionGroupAPIMock = _OptionGroupAPIMock_;
      OptionGroup = _OptionGroup_;

      spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
        return OptionGroupAPIMock.valuesOf(name);
      });
      spyOn(LeaveRequestAPI, 'all').and.callThrough();
      spyOn(LeaveRequestAPI, 'find').and.callThrough();
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
      var leaveRequestPromise;

      beforeEach(function () {
        leaveRequestPromise = LeaveRequest.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String));
      });

      afterEach(function () {
        // to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        leaveRequestPromise.then(function () {
          expect(LeaveRequestAPI.balanceChangeByAbsenceType).toHaveBeenCalled();
        });
      });
    });

    describe('calculateBalanceChange()', function () {
      var promise;

      beforeEach(function () {
        promise = LeaveRequest.calculateBalanceChange(jasmine.any(Object));
      });

      afterEach(function () {
        // to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
        });
      });
    });

    describe('find()', function () {
      var leaveRequestPromise;

      beforeEach(function () {
        leaveRequestPromise = LeaveRequest.find();
      });

      it('calls equivalent API method', function () {
        leaveRequestPromise.then(function () {
          expect(LeaveRequestAPI.find).toHaveBeenCalled();
        });
      });

      it('returns model instances', function () {
        leaveRequestPromise.then(function (response) {
          expect(('cancel' in response) && ('update' in response)).toBe(true);
        });
      });
    });
  });
});
