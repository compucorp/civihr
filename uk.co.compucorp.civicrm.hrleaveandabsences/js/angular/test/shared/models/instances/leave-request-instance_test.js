define([
  'mocks/data/leave-request-data',
  'mocks/apis/leave-request-api-mock',
  'leave-absences/shared/models/instances/leave-request-instance',
  'leave-absences/shared/modules/models',
], function (mockData) {
  'use strict';

  describe('LeaveRequestInstance', function () {
    var $provide,
      LeaveRequestInstance,
      LeaveRequestAPI,
      $q,
      OptionGroup,
      $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.models.instances', 'leave-absences.mocks',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_LeaveRequestAPIMock_) {
      //LeaveRequestAPI is internally used by Model and hence need to be mocked
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
    }));

    beforeEach(inject([
      "LeaveRequestInstance",
      "LeaveRequestAPI",
      "$rootScope",
      "$q",
      'api.optionGroup',
      function (_LeaveRequestInstance_, _LeaveRequestAPI_, _$rootScope_, _$q_, _OptionGroup_) {
        LeaveRequestInstance = _LeaveRequestInstance_;
        LeaveRequestAPI = _LeaveRequestAPI_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        OptionGroup = _OptionGroup_;
      }
    ]));

    describe('cancel()', function () {
      var optionGroupDeferred,
        leaveRequestDeferred,
        mockOptionValue,
        mockUpdateResponse,
        promise;

      function commonSetup(returnData) {
        optionGroupDeferred = $q.defer();
        leaveRequestDeferred = $q.defer();
        mockOptionValue = [{
          name: "cancelled",
          value: "1"
        }];
        mockUpdateResponse = returnData;

        spyOn(OptionGroup, 'valuesOf').and.returnValue(optionGroupDeferred.promise);
        spyOn(LeaveRequestInstance, 'update').and.returnValue(leaveRequestDeferred.promise);

        optionGroupDeferred.resolve(mockOptionValue);
        leaveRequestDeferred.resolve(mockUpdateResponse);

        promise = LeaveRequestInstance.cancel();
        LeaveRequestInstance.status_id = jasmine.any(String);
      }

      afterEach(function () {
        $rootScope.$apply();
      });

      describe("success", function () {

        beforeEach(function () {
          commonSetup(mockData.singleDataSuccess());
        });

        it('updates the status_id of the instance', function () {
          promise.then(function () {
            expect(LeaveRequestInstance.status_id).toBe(mockUpdateResponse.values[0].status_id);
          });
        });

        it('OptionGroup.valuesOf gets called', function () {
          promise.then(function () {
            expect(OptionGroup.valuesOf).toHaveBeenCalledWith('hrleaveandabsences_leave_request_status');
          });
        });

        it('LeaveRequestInstance.update gets called', function () {
          promise.then(function () {
            expect(LeaveRequestInstance.update).toHaveBeenCalledWith({
              'status_id': mockOptionValue[0].value
            });
          });
        });
      });

      describe("error", function () {

        beforeEach(function () {
          commonSetup(mockData.singleDataError());
        });

        it('updates the status_id of the instance', function () {
          promise.then(function (data) {
            expect(data).toBe(mockUpdateResponse);
          });
        });
      })
    });

    describe('update()', function () {
      var requestData, promise;

      beforeEach(function () {
        requestData = mockData.createRandomLeaveRequest();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        promise = LeaveRequestAPI.update(requestData);
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.update).toHaveBeenCalled();
        });
      });
    });

    describe('create()', function () {
      var requestData, promise;

      beforeEach(function () {
        requestData = mockData.createRandomLeaveRequest();
        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        promise = LeaveRequestAPI.create(requestData);
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.create).toHaveBeenCalled();
        });
      });

      it('id is appended to instance', function () {
        expect(requestData.id).not.toBeDefined();
        promise.then(function (result) {
          expect(result.id).toBeDefined();
        });
      });
    });

    describe('isValid()', function () {
      var requestData, promise;

      beforeEach(function () {
        requestData = mockData.createRandomLeaveRequest();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
        promise = LeaveRequestAPI.isValid(requestData);
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
        });
      });
    });
  });
});
