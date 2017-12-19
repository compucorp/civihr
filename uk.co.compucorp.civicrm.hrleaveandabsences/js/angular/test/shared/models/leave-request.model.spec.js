/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/leave-request-data',
  'leave-absences/shared/instances/leave-request.instance',
  'leave-absences/shared/instances/sickness-request.instance',
  'leave-absences/shared/instances/toil-request.instance',
  'leave-absences/shared/models/leave-request.model',
  'mocks/apis/leave-request-api-mock'
], function (_, LeaveRequestData) {
  'use strict';

  describe('LeaveRequest', function () {
    var $provide, $rootScope, LeaveRequest, LeaveRequestAPI,
      OptionGroup, OptionGroupAPIMock, requestInstances;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
    }));

    beforeEach(inject(function (_$rootScope_, _LeaveRequest_, _LeaveRequestAPI_,
      _LeaveRequestInstance_, _OptionGroup_, _OptionGroupAPIMock_,
      _SicknessRequestInstance_, _TOILRequestInstance_) {
      $rootScope = _$rootScope_;
      LeaveRequest = _LeaveRequest_;
      LeaveRequestAPI = _LeaveRequestAPI_;
      OptionGroup = _OptionGroup_;
      OptionGroupAPIMock = _OptionGroupAPIMock_;
      requestInstances = {
        'leave': _LeaveRequestInstance_,
        'sickness': _SicknessRequestInstance_,
        'toil': _TOILRequestInstance_
      };

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
      var leaveRequestPromiseResult;

      beforeEach(function () {
        LeaveRequest.all().then(function (promiseResult) {
          leaveRequestPromiseResult = promiseResult;
        });

        $rootScope.$digest();
      });

      it('calls equivalent API method', function () {
        expect(LeaveRequestAPI.all).toHaveBeenCalled();
      });

      it('returns a collection of request instances types of which correspond to request types', function () {
        // It is expected that all types of leave requests are mocked
        expect(leaveRequestPromiseResult.list.every(function (modelInstance) {
          return getRequestInstanceType(modelInstance) === modelInstance.request_type;
        })).toBe(true);
      });
    });

    describe('balanceChangeByAbsenceType()', function () {
      beforeEach(function () {
        LeaveRequest.balanceChangeByAbsenceType(1, 2, 3, 4);
        $rootScope.$digest();
      });

      it('calls equivalent API method', function () {
        expect(LeaveRequestAPI.balanceChangeByAbsenceType).toHaveBeenCalledWith(1, 2, 3, 4);
      });
    });

    describe('find()', function () {
      var leaveRequestPromiseResult;

      describe('basic tests', function () {
        beforeEach(function () {
          var requestId = LeaveRequestData.all().values[0].id;

          LeaveRequest.find(requestId)
            .then(function (promiseResult) {
              leaveRequestPromiseResult = promiseResult;
            });

          $rootScope.$digest();
        });

        it('calls equivalent API method', function () {
          expect(LeaveRequestAPI.find).toHaveBeenCalled();
        });
      });

      ['leave', 'sickness', 'toil'].forEach(function (requestType) {
        describe('when the request type is "' + requestType + '"', function () {
          beforeEach(function () {
            var requestId = LeaveRequestData.findBy('request_type', requestType).id;

            LeaveRequest.find(requestId).then(function (promiseResult) {
              leaveRequestPromiseResult = promiseResult;
            });
            $rootScope.$digest();
          });

          it('it returns a model instance of the corresponding type', function () {
            expect(getRequestInstanceType(leaveRequestPromiseResult)).toBe(leaveRequestPromiseResult.request_type);
          });
        });
      });
    });

    /**
     * Gets request instance type of the given model instance
     * by comparing all methods in the given model instance
     * with all methods in each request instance
     *
     * @param  {Object} modelInstance
     * @return {Boolean}
     */
    function getRequestInstanceType (modelInstance) {
      return _.findKey(requestInstances, function (requestInstance) {
        return _.isEqual(
          _.difference(_.keysIn(modelInstance), _.keys(modelInstance)).sort(),
          _.keysIn(requestInstance).sort());
      });
    }
  });
});
