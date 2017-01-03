define([
  'mocks/data/leave-request-data',
  'mocks/data/option-group-mock-data',
  'mocks/helpers/helper',
  'mocks/apis/leave-request-api-mock',
  'leave-absences/shared/models/instances/leave-request-instance',
  'leave-absences/shared/modules/models',
], function (leaveRequestMockData, optionGroupMockData, helper) {
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
        LeaveRequestInstance = _LeaveRequestInstance_.init({}, false);
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

      afterEach(function () {
        $rootScope.$apply();
      });

      describe('cancel', function () {

        describe('success', function () {

          beforeEach(function () {
            commonSetup('cancelled', 'cancel', leaveRequestMockData.singleDataSuccess());
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
            commonSetup('cancelled', 'cancel', leaveRequestMockData.singleDataError());
          });

          it('updates the status_id of the instance', function () {
            promise.then(function (data) {
              expect(data).toBe(mockUpdateResponse);
            });
          });
        })
      });

      describe('approve', function () {

        describe('success', function () {

          beforeEach(function () {
            commonSetup('approved', 'approve', leaveRequestMockData.singleDataSuccess());
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
            commonSetup('approved', 'approve', leaveRequestMockData.singleDataError());
          });

          it('updates the status_id of the instance', function () {
            promise.then(function (data) {
              expect(data).toBe(mockUpdateResponse);
            });
          });
        })
      });

      describe('reject', function () {

        describe('success', function () {

          beforeEach(function () {
            commonSetup('rejected', 'reject', leaveRequestMockData.singleDataSuccess());
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
            commonSetup('rejected', 'reject', leaveRequestMockData.singleDataError());
          });

          it('updates the status_id of the instance', function () {
            promise.then(function (data) {
              expect(data).toBe(mockUpdateResponse);
            });
          });
        })
      });

      describe('sendBack', function () {

        describe('success', function () {

          beforeEach(function () {
            commonSetup('more_information_requested', 'sendBack', leaveRequestMockData.singleDataSuccess());
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
            commonSetup('more_information_requested', 'sendBack', leaveRequestMockData.singleDataError());
          });

          it('updates the status_id of the instance', function () {
            promise.then(function (data) {
              expect(data).toBe(mockUpdateResponse);
            });
          });
        })
      });

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
    });

    describe('update()', function () {
      var instanceUpdate, newRequestData;

      beforeEach(function () {
        var changedStatusId = {
          status_id: leaveRequestMockData.all().values[5].status_id
        };
        requestData = leaveRequestMockData.all().values[0];
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

    describe('check status methods', function () {

      var promise;

      beforeEach(function () {
        spyOn(OptionGroup, 'valuesOf').and.callFake(function () {
          return $q.resolve(optionGroupMockData.getCollection('hrleaveandabsences_leave_request_status'));
        });
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      describe('isApproved', function () {

        describe('status is approved', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('approved');
            promise = LeaveRequestInstance.isApproved();
          });

          it('returns true', function () {
            promise.then(function (data) {
              expect(data).toBe(true);
            })
          });
        });

        describe('status is not approved', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('cancelled');
            promise = LeaveRequestInstance.isApproved();
          });

          it('returns false', function () {
            promise.then(function (data) {
              expect(data).toBe(false);
            })
          });
        });
      });

      describe('isAwaitingApproval', function () {

        describe('status is waiting_approval', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('waiting_approval');
            promise = LeaveRequestInstance.isAwaitingApproval();
          });

          it('returns true', function () {
            promise.then(function (data) {
              expect(data).toBe(true);
            })
          });
        });

        describe('status is not waiting_approval', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('cancelled');
            promise = LeaveRequestInstance.isAwaitingApproval();
          });

          it('returns false', function () {
            promise.then(function (data) {
              expect(data).toBe(false);
            })
          });
        });
      });

      describe('isCancelled', function () {

        describe('status is cancelled', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('cancelled');
            promise = LeaveRequestInstance.isCancelled();
          });

          it('returns true', function () {
            promise.then(function (data) {
              expect(data).toBe(true);
            })
          });
        });

        describe('status is not cancelled', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('approved');
            promise = LeaveRequestInstance.isCancelled();
          });

          it('returns false', function () {
            promise.then(function (data) {
              expect(data).toBe(false);
            })
          });
        });
      });

      describe('isRejected', function () {

        describe('status is rejected', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('rejected');
            promise = LeaveRequestInstance.isRejected();
          });

          it('returns true', function () {
            promise.then(function (data) {
              expect(data).toBe(true);
            })
          });
        });

        describe('status is not rejected', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('approved');
            promise = LeaveRequestInstance.isRejected();
          });

          it('returns false', function () {
            promise.then(function (data) {
              expect(data).toBe(false);
            })
          });
        });
      });

      describe('isSentBack', function () {

        describe('status is more_information_requested', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('more_information_requested');
            promise = LeaveRequestInstance.isSentBack();
          });

          it('returns true', function () {
            promise.then(function (data) {
              expect(data).toBe(true);
            })
          });
        });

        describe('status is not more_information_requested', function () {

          beforeEach(function () {
            LeaveRequestInstance.status_id = getStatusIdByName('approved');
            promise = LeaveRequestInstance.isSentBack();
          });

          it('returns false', function () {
            promise.then(function (data) {
              expect(data).toBe(false);
            })
          });
        });
      });

      function getStatusIdByName(statusName) {
        return optionGroupMockData.getCollection('hrleaveandabsences_leave_request_status').find(function (option) {
          return option.name === statusName
        }).value;
      }
    });

    describe('roleOf()', function () {

      var promise,
        defer;

      afterEach(function () {
        $rootScope.$apply();
      });

      describe('when contact_id of leave request is same as contact id of parameter', function () {

        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'isManagedBy').and.callThrough();
          //dummy contact id
          LeaveRequestInstance.contact_id = "101";
          promise = LeaveRequestInstance.roleOf({
            id: '101'
          });
        });

        it('returns owner', function () {
          promise.then(function (result) {
            expect(result).toBe('owner');
          });
        })
      });

      describe('when contact_id of leave request is not same as contact id of parameter', function () {

        describe('when api returns error', function () {

          beforeEach(function () {
            spyOn(LeaveRequestAPI, 'isManagedBy').and.callFake(function () {
              defer = $q.defer();
              defer.resolve({
                is_error: true
              });
              return defer.promise;
            });
            commonSetup();
          });

          it('returns error', function () {
            promise.then(function (result) {
              expect(result).toBe('error');
            });
          })
        });

        describe('when isManagedBy return true', function () {

          beforeEach(function () {
            spyOn(LeaveRequestAPI, 'isManagedBy').and.callFake(function () {
              defer = $q.defer();
              defer.resolve({
                values: true
              });
              return defer.promise;
            });
            commonSetup();
          });

          it('returns manager', function () {
            promise.then(function (result) {
              expect(result).toBe('manager');
            });
          })
        });

        describe('when user has no specific role', function () {

          beforeEach(function () {
            spyOn(LeaveRequestAPI, 'isManagedBy').and.callFake(function () {
              defer = $q.defer();
              defer.resolve({
                values: false
              });
              return defer.promise;
            });
            commonSetup();
          });

          it('returns none', function () {
            promise.then(function (result) {
              expect(result).toBe('none');
            });
          })
        });

        function commonSetup() {
          //dummy contact id
          LeaveRequestInstance.contact_id = "101";
          promise = LeaveRequestInstance.roleOf({
            id: '102' //not same as instance.contact_id
          });
        }
      });
    });
  });
});
