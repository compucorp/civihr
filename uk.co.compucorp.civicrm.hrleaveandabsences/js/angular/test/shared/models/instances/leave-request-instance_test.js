define([
  'mocks/data/leave-request-data',
  'mocks/helpers/helper',
  'mocks/apis/leave-request-api-mock',
  'leave-absences/shared/models/instances/leave-request-instance',
  'leave-absences/shared/modules/models',
], function (mockData, helper) {
  'use strict';

  describe('LeaveRequestInstance', function () {
    var $provide,
      LeaveRequestInstance,
      LeaveRequestAPI,
      $q,
      OptionGroup,
      $rootScope,
      instance,
      requestData,
      expectedError;

    beforeEach(module('leave-absences.models', 'leave-absences.models.instances', 'leave-absences.mocks',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_LeaveRequestAPIMock_) {
      //LeaveRequestAPI is internally used by Model and hence need to be mocked
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
    }));

    beforeEach(inject([
      'LeaveRequestInstance',
      'LeaveRequestAPI',
      '$rootScope',
      '$q',
      'api.optionGroup',
      function (_LeaveRequestInstance_, _LeaveRequestAPI_, _$rootScope_, _$q_, _OptionGroup_) {
        LeaveRequestInstance = _LeaveRequestInstance_;
        LeaveRequestAPI = _LeaveRequestAPI_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        OptionGroup = _OptionGroup_;

        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
      }
    ]));

    describe('status change methods', function () {
      var optionGroupDeferred,
        leaveRequestDeferred,
        mockOptionValue,
        mockUpdateResponse,
        promise;

      function commonSetup(statusName, methodName, returnData) {
        optionGroupDeferred = $q.defer();
        leaveRequestDeferred = $q.defer();
        mockOptionValue = [{
          name: statusName,
          value: '1'
        }];
        mockUpdateResponse = returnData;

        spyOn(OptionGroup, 'valuesOf').and.returnValue(optionGroupDeferred.promise);
        spyOn(LeaveRequestInstance, 'update').and.returnValue(leaveRequestDeferred.promise);

        optionGroupDeferred.resolve(mockOptionValue);
        leaveRequestDeferred.resolve(mockUpdateResponse);

        promise = LeaveRequestInstance[methodName]();
        LeaveRequestInstance.status_id = jasmine.any(String);
      }

      afterEach(function () {
        $rootScope.$apply();
      });

      describe('cancel', function() {

        describe('success', function () {

          beforeEach(function () {
            commonSetup('cancelled', 'cancel', mockData.singleDataSuccess());
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
              expect(LeaveRequestInstance.update).toHaveBeenCalled();
              expect(LeaveRequestInstance.status_id).toEqual(mockOptionValue[0].value);
            });
          });
        });

        describe('error', function () {

          beforeEach(function () {
            commonSetup('cancelled', 'cancel', mockData.singleDataError());
          });

          it('updates the status_id of the instance', function () {
            promise.then(function (data) {
              expect(data).toBe(mockUpdateResponse);
            });
          });
        })
      });

      describe('approve', function() {

        describe('success', function () {

          beforeEach(function () {
            commonSetup('approved', 'approve', mockData.singleDataSuccess());
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
              expect(LeaveRequestInstance.update).toHaveBeenCalled();
              expect(LeaveRequestInstance.status_id).toEqual(mockOptionValue[0].value);
            });
          });
        });

        describe('error', function () {

          beforeEach(function () {
            commonSetup('approved', 'approve', mockData.singleDataError());
          });

          it('updates the status_id of the instance', function () {
            promise.then(function (data) {
              expect(data).toBe(mockUpdateResponse);
            });
          });
        })
      });

      describe('reject', function() {

        describe('success', function () {

          beforeEach(function () {
            commonSetup('rejected', 'reject', mockData.singleDataSuccess());
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
              expect(LeaveRequestInstance.update).toHaveBeenCalled();
              expect(LeaveRequestInstance.status_id).toEqual(mockOptionValue[0].value);
            });
          });
        });

        describe('error', function () {

          beforeEach(function () {
            commonSetup('rejected', 'reject', mockData.singleDataError());
          });

          it('updates the status_id of the instance', function () {
            promise.then(function (data) {
              expect(data).toBe(mockUpdateResponse);
            });
          });
        })
      });

      describe('sendBack', function() {

        describe('success', function () {

          beforeEach(function () {
            commonSetup('more_information_requested', 'sendBack', mockData.singleDataSuccess());
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
              expect(LeaveRequestInstance.update).toHaveBeenCalled();
              expect(LeaveRequestInstance.status_id).toEqual(mockOptionValue[0].value);
            });
          });
        });

        describe('error', function () {

          beforeEach(function () {
            commonSetup('more_information_requested', 'sendBack', mockData.singleDataError());
          });

          it('updates the status_id of the instance', function () {
            promise.then(function (data) {
              expect(data).toBe(mockUpdateResponse);
            });
          });
        })
      });
    });

    describe('update()', function () {
      var instanceUpdate, newRequestData;

      beforeEach(function () {
        var changedStatusId = {
          status_id: mockData.all().values[5].status_id
        };
        requestData = mockData.all().values[0];
        instance = LeaveRequestInstance.init(requestData, false);
        newRequestData = _.assign({}, requestData, changedStatusId);
      });

      describe('when id is set', function () {

        beforeEach(function () {
          _.assign(instance, newRequestData);
          instanceUpdate = instance.update();
        });

        afterEach(function () {
          //to excute the promise force an digest
          $rootScope.$apply();
        });

        it('calls equivalent API method', function () {
          instanceUpdate.then(function () {
            expect(LeaveRequestAPI.update).toHaveBeenCalled();
          });
        });

        it('modifies attributes', function () {
          var updatedAttributes = _.assign(Object.create(null), requestData, newRequestData);

          instanceUpdate.then(function () {
            expect(instance.attributes()).toEqual(jasmine.objectContaining(updatedAttributes));
          });
        });
      });

      describe('when id is missing', function () {

        beforeEach(function () {
          expectedError = {
            is_error: 1,
            error_message: 'id is mandatory field'
          };
          delete instance.id;
          instanceUpdate = instance.update();
        });

        afterEach(function () {
          //to excute the promise force an digest
          $rootScope.$apply();
        });

        it('fails to update attributes ', function () {
          instanceUpdate.catch(function (error) {
            expect(error).toEqual(jasmine.objectContaining(expectedError));
          });
        });
      });
    });

    describe('create()', function () {
      var instanceCreate;

      beforeEach(function () {
        requestData = helper.createRandomLeaveRequest();
        instance = LeaveRequestInstance.init(requestData, false);
        instanceCreate = instance.create();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        instanceCreate.then(function () {
          expect(LeaveRequestAPI.create).toHaveBeenCalled();
        });
      });

      it('id is appended to instance', function () {
        expect(instance.id).not.toBeDefined();
        instanceCreate.then(function () {
          expect(instance.id).toBeDefined();
          expect(instance.id).toEqual(jasmine.any(String));
        });
      });

      describe('when one mandatory filed is missing', function () {

        beforeEach(function () {
          expectedError = {
            is_error: 1,
            error_message: 'contact_id, from_date and from_date_type in params are mandatory'
          };
          delete instance.contact_id;
          instanceCreate = instance.create();
        });

        afterEach(function () {
          //to excute the promise force an digest
          $rootScope.$apply();
        });

        it('fails to create instance', function () {
          instanceCreate.catch(function (error) {
            expect(error).toEqual(jasmine.objectContaining(expectedError));
          });
        });
      });
    });

    describe('isValid()', function () {
      var instanceValid;

      beforeEach(function () {
        requestData = {
          contact_id: '123'
        };
        instance = LeaveRequestInstance.init(requestData, false);
        instanceValid = instance.isValid();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        instanceValid.then(function () {
          expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
        });
      });

      describe('when leave request is valid', function () {
        it('returns no error', function () {
          instanceValid.then(function (result) {
            expect(result).toEqual([]);
          });
        });

        describe('when valid data not present', function () {

          beforeEach(function () {
            delete instance.contact_id;
            instanceValid = instance.isValid();
          });

          afterEach(function () {
            //to excute the promise force an digest
            $rootScope.$apply();
          });

          it('returns array of errors', function () {
            instanceValid.catch(function (result) {
              expect(Object.keys(result).length).toBeGreaterThan(0);
            });
          });
        });
      });
    });
  });
});
