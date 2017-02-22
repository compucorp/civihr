define([
  'mocks/data/leave-request-data',
  'mocks/data/option-group-mock-data',
  'mocks/data/comments-data',
  'mocks/helpers/helper',
  'mocks/apis/leave-request-api-mock',
  'leave-absences/shared/models/instances/leave-request-instance',
  'leave-absences/shared/modules/models',
], function (leaveRequestMockData, optionGroupMockData, commentsData, helper) {
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
      'OptionGroup',
      function (_LeaveRequestInstance_, _LeaveRequestAPI_, _$rootScope_, _$q_, _OptionGroup_) {
        LeaveRequestInstance = _LeaveRequestInstance_.init({}, false);
        LeaveRequestAPI = _LeaveRequestAPI_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        OptionGroup = _OptionGroup_;

        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
        spyOn(LeaveRequestAPI, 'saveComment').and.callThrough();
      }
    ]));

    describe('default values', function () {
      it('comments are empty', function () {
        expect(LeaveRequestInstance.comments.length).toBe(0);
      })
    });

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
      var promise,
        toAPIReturnValue = {
          key: jasmine.any(String)
        };

      beforeEach(function () {
        var defer = $q.defer();
        LeaveRequestAPI.update.and.returnValue(defer.promise);
        defer.resolve(jasmine.any(Object));
        spyOn(LeaveRequestInstance, 'toAPI').and.returnValue(toAPIReturnValue);
        LeaveRequestInstance.comments = commentsData.getCommentsWithMixedIDs().values;

        promise = LeaveRequestInstance.update();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls update api method with the return value of toAPI method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.update).toHaveBeenCalledWith(toAPIReturnValue);
        });
      });

      it('calls toAPI method', function () {
        promise.then(function () {
          expect(LeaveRequestInstance.toAPI).toHaveBeenCalled();
        });
      });

      it('calls API to save the newly created comments only(without having ID)', function () {
        promise.then(function () {
          commentsData.getCommentsWithMixedIDs().values.map(function (comment) {
            if (!comment.comment_id) {
              expect(LeaveRequestAPI.saveComment).toHaveBeenCalledWith(LeaveRequestInstance.id, comment);
            }
          });
        });
      });
    });

    describe('create()', function () {
      var instanceCreate;

      beforeEach(function () {
        requestData = helper.createRandomLeaveRequest();
        instance = LeaveRequestInstance.init(requestData, false);
        instance.comments = commentsData.getCommentsWithMixedIDs().values;
        instanceCreate = instance.create();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method without comments', function () {
        instanceCreate.then(function () {
          expect(LeaveRequestAPI.create).toHaveBeenCalledWith(jasmine.objectContaining({
            comments: null
          }));
        });
      });

      it('id is appended to instance', function () {
        expect(instance.id).not.toBeDefined();
        instanceCreate.then(function () {
          expect(instance.id).toBeDefined();
          expect(instance.id).toEqual(jasmine.any(String));
        });
      });

      it('comments are set back after API call', function () {
        instanceCreate.then(function () {
          expect(instance.comments).not.toEqual(null);
        });
      });

      it('calls API to save the newly created comments only(without having ID)', function () {
        instanceCreate.then(function () {
          commentsData.getCommentsWithMixedIDs().values.map(function (comment) {
            if (!comment.comment_id) {
              expect(LeaveRequestAPI.saveComment).toHaveBeenCalledWith(instance.id, comment);
            }
          });
        });
      });

      describe('when one mandatory filed is missing', function () {

        beforeEach(function () {
          expectedError = 'contact_id, from_date and from_date_type in params are mandatory';
          delete instance.contact_id;
          instanceCreate = instance.create();
        });

        afterEach(function () {
          //to excute the promise force an digest
          $rootScope.$apply();
        });

        it('fails to create instance', function () {
          instanceCreate.catch(function (error) {
            expect(error).toBe(expectedError);
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

      var promise;

      afterEach(function () {
        $rootScope.$apply();
      });

      describe('when the user is the owner of the leave request', function () {

        beforeEach(function () {
          setUserAsOwner();
        });

        it('returns owner', function () {
          promise.then(function (result) {
            expect(result).toBe('owner');
          });
        })
      });

      describe('when the user is the manager', function () {

        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'isManagedBy').and.callFake(function () {
            return setIsManagedResponseTo(true);
          });
          setUserAsNotOwner();
        });

        it('returns manager', function () {
          promise.then(function (result) {
            expect(result).toBe('manager');
          });
        })
      });

      describe('when the user has no relationship', function () {

        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'isManagedBy').and.callFake(function () {
            return setIsManagedResponseTo(false);
          });
          setUserAsNotOwner();
        });

        it('returns none', function () {
          promise.then(function (result) {
            expect(result).toBe('none');
          });
        })
      });

      function setUserAsNotOwner() {
        //dummy contact id
        LeaveRequestInstance.contact_id = "101";
        promise = LeaveRequestInstance.roleOf({
          id: '102' //not same as instance.contact_id
        });
      }

      function setUserAsOwner() {
        //dummy contact id
        LeaveRequestInstance.contact_id = "101";
        promise = LeaveRequestInstance.roleOf({
          id: '101'
        });
      }

      function setIsManagedResponseTo(value) {
        return $q.resolve(value);
      }
    });

    // Testing the customization of toAPIFilter via toAPI, as the former
    // is just an implementation detail, exposed so it can be customized
    describe('toAPI()', function () {
      var leaveRequest, toAPIData;

      beforeEach(function () {
        leaveRequest = LeaveRequestInstance.init(leaveRequestMockData.all().values[1], true);
        leaveRequest.balance_change = _.random(-10, -5);
        leaveRequest.dates = jasmine.any(Array);

        toAPIData = leaveRequest.toAPI();
      });

      it('filters out the `balance_change` and `dates` properties', function () {
        expect(Object.keys(toAPIData)).toEqual(_.without(
          Object.keys(leaveRequest.attributes()),
          'balance_change',
          'dates'
        ));
      });
    });
  });
});
