/* eslint-env amd, jasmine */
define([
  'common/lodash',
  'common/moment',
  'mocks/data/leave-request-data',
  'mocks/data/sickness-leave-request-data',
  'mocks/data/toil-leave-request-data',
  'mocks/data/comments-data',
  'mocks/helpers/helper',
  'mocks/data/absence-type-data',
  'mocks/data/option-group-mock-data',
  'leave-absences/shared/apis/leave-request-api',
  'leave-absences/shared/modules/shared-settings'
], function (_, moment, mockData, sicknessMockData, toilMockData, commentsData, helper, absenceTypeData, optionGroupMock) {
  'use strict';

  describe('LeaveRequestAPI', function () {
    var LeaveRequestAPI, $httpBackend, $rootScope, $q, sharedSettings,
      promise, requestData, errorMessage;

    beforeEach(module('leave-absences.apis', 'leave-absences.settings'));

    beforeEach(inject(['LeaveRequestAPI', '$httpBackend', '$rootScope', '$q', 'shared-settings',
      function (_LeaveRequestAPI_, _$httpBackend_, _$rootScope_, _$q_, _sharedSettings_) {
        LeaveRequestAPI = _LeaveRequestAPI_;
        $httpBackend = _$httpBackend_;
        $rootScope = _$rootScope_;
        sharedSettings = _sharedSettings_;
        $q = _$q_;

        interceptHTTP();
      }
    ]));

    describe('all()', function () {
      describe('leave request', function () {
        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'getAll').and.callThrough();
          promise = LeaveRequestAPI.all();
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls the getAll() method', function () {
          expect(LeaveRequestAPI.getAll.calls.mostRecent().args[0]).toBe('LeaveRequest');
          expect(LeaveRequestAPI.getAll.calls.mostRecent().args[5]).toBe('getFull');
        });

        it('returns all the data', function () {
          promise.then(function (response) {
            expect(response.list).toEqual(mockData.all().values);
          });
        });
      });
    });

    describe('balanceChangeByAbsenceType()', function () {
      describe('without errors from server ', function () {
        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
        });

        describe('error handling', function () {
          afterEach(function () {
            $rootScope.$apply();
          });

          function commonExpect (data) {
            expect(data).toBe('contact_id and period_id are mandatory');
          }

          it('throws error if contact_id is blank', function () {
            LeaveRequestAPI.balanceChangeByAbsenceType(null, jasmine.any(String))
              .catch(commonExpect);
          });

          it('throws error if periodId is blank', function () {
            LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), null)
              .catch(commonExpect);
          });
        });

        describe('default values', function () {
          afterEach(function () {
            $httpBackend.flush();
          });

          it('status and publicHoliday has default values if falsy values has been passed', function () {
            LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String));

            expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', jasmine.objectContaining({
              contact_id: jasmine.any(String),
              period_id: jasmine.any(String),
              statuses: null,
              public_holiday: false
            }), false);
          });

          it('sends as `public_holiday` the original value if truthy value had been passed', function () {
            LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), true);

            expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', jasmine.objectContaining({
              public_holiday: true
            }), false);
          });

          it('sends as `statuses` an "IN" list if the original value is an array', function () {
            LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), jasmine.any(Array), jasmine.any(Boolean));

            expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getbalancechangebyabsencetype', jasmine.objectContaining({
              statuses: {
                'IN': jasmine.any(Array)
              }
            }), false);
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

    describe('calculateBalanceChange()', function () {
      describe('without error from server', function () {
        beforeEach(function () {
          requestData = helper.createRandomLeaveRequest();
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
          promise = LeaveRequestAPI.calculateBalanceChange(requestData);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls endpoint', function () {
          promise.then(function (result) {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalled();
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith(jasmine.any(String),
              jasmine.any(String), jasmine.any(Object));
          });
        });

        it('returns expected data keys', function () {
          promise.then(function (result) {
            // returns an object(associative array) and not an array
            var breakdown = result.breakdown[0];
            var breakdownType = breakdown.type;

            expect(result.amount).toBeDefined();
            expect(result.breakdown).toBeDefined();
            expect(breakdown.date).toBeDefined();
            expect(breakdown.amount).toBeDefined();
            expect(breakdown.type).toBeDefined();
            expect(breakdownType.id).toBeDefined();
            expect(breakdownType.value).toBeDefined();
            expect(breakdownType.label).toBeDefined();
          });
        });

        it('returns expected values', function () {
          promise.then(function (result) {
            var breakdown = result.breakdown[0];
            var breakdownType = breakdown.type;

            expect(result.amount).toEqual(jasmine.any(Number));
            expect(result.breakdown).toEqual(jasmine.any(Object));
            expect(breakdown.amount).toEqual(jasmine.any(Number));
            expect(moment(breakdown.date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
            expect(breakdown.type).toEqual(jasmine.any(Object));
            expect(breakdownType.id).toEqual(jasmine.any(Number));
            expect(breakdownType.value).toEqual(jasmine.any(Number));
            expect(absenceTypeData.getAllAbsenceTypesTitles()).toContain(breakdownType.label);
          });
        });

        describe('when mandatory field is missing', function () {
          beforeEach(function () {
            errorMessage = 'contact_id, from_date and from_date_type in params are mandatory';
            requestData = {};
            promise = LeaveRequestAPI.calculateBalanceChange(requestData);
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('throws an error', function () {
            promise.catch(function (result) {
              expect(result).toBe(errorMessage);
            });
          });
        });
      });
    });

    describe('create()', function () {
      describe('without error from server', function () {
        beforeEach(function () {
          requestData = helper.createRandomLeaveRequest();
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
          promise = LeaveRequestAPI.create(requestData);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'create', requestData);
          });
        });

        it('returns expected keys', function () {
          promise.then(function (result) {
            expect(result.id).toBeDefined();
            expect(result.type_id).toBeDefined();
            expect(result.contact_id).toBeDefined();
            expect(result.status_id).toBeDefined();
            expect(result.from_date).toBeDefined();
            expect(moment(result.from_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
            expect(result.from_date_type).toBeDefined();
          });
        });

        it('returns expected values', function () {
          promise.then(function (result) {
            expect(result.id).toEqual(jasmine.any(String));
            expect(result.type_id).toBeDefined();
            expect(absenceTypeData.getAllAbsenceTypesIds()).toContain(result.type_id);
            expect(result.contact_id).toEqual(jasmine.any(String));
            expect(optionGroupMock.getAllRequestStatusesValues()).toContain(result.status_id);
            expect(moment(result.from_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
            expect(optionGroupMock.getAllRequestDayValues()).toContain(result.from_date_type);
          });
        });

        describe('with mandatory field missing', function () {
          beforeEach(function () {
            errorMessage = 'contact_id, from_date, status_id and from_date_type params are mandatory';
            requestData = helper.createRandomLeaveRequest();
            delete requestData.contact_id;
            promise = LeaveRequestAPI.create(requestData);
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('returns error', function () {
            promise.catch(function (result) {
              expect(result).toBe(errorMessage);
            });
          });
        });

        describe('missing to date type value, given to date', function () {
          beforeEach(function () {
            errorMessage = 'to_date_type is mandatory';
            requestData = helper.createRandomLeaveRequest();
            delete requestData.to_date_type;
            promise = LeaveRequestAPI.create(requestData);
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('returns error', function () {
            promise.catch(function (result) {
              expect(result).toBe(errorMessage);
            });
          });
        });
      });
    });

    describe('isValid()', function () {
      describe('without error from server', function () {
        describe('when called with valid data', function () {
          beforeEach(function () {
            requestData = helper.createRandomSicknessRequest();
            spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
            promise = LeaveRequestAPI.isValid(requestData);
          });

          afterEach(function () {
            $httpBackend.flush();
          });

          it('calls endpoint', function () {
            promise.then(function () {
              expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'isValid', requestData);
            });
          });

          it('returns no errors', function () {
            promise.then(function (result) {
              expect(result).toEqual([]);
            });
          });
        });

        describe('when called with invalid data', function () {
          beforeEach(function () {
            requestData = helper.createRandomSicknessRequest();
            spyOn(LeaveRequestAPI, 'sendPOST').and.callFake(function (params) {
              return $q.resolve(mockData.getNotIsValid());
            });
            promise = LeaveRequestAPI.isValid(requestData);
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('rejects promise with validation errors', function () {
            var errors = _(mockData.getNotIsValid().values).map().flatten().value();

            promise.catch(function (result) {
              expect(result).toEqual(errors);
            });
          });
        });
      });
    });

    describe('update()', function () {
      describe('without error from server', function () {
        var updatedRequestData = {};

        beforeEach(function () {
          var changedStatusId = {
            status_id: mockData.all().values[5].status_id
          };
          requestData = mockData.all().values[0];
          requestData = _.assign(updatedRequestData, requestData, changedStatusId);
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
          promise = LeaveRequestAPI.update(updatedRequestData);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'create', requestData);
          });
        });

        it('returns updated leave request', function () {
          promise.then(function (result) {
            expect(result.id).toBeDefined();
          });
        });

        describe('when id is not set', function () {
          beforeEach(function () {
            errorMessage = 'id is mandatory field';
            // remove id
            delete updatedRequestData.id;
            promise = LeaveRequestAPI.update(updatedRequestData);
          });

          afterEach(function () {
            // resolves to local promise hence no need to flush http call
            $rootScope.$apply();
          });

          it('returns error', function () {
            promise.catch(function (result) {
              expect(result).toBe(errorMessage);
            });
          });
        });
      });
    });

    describe('getComments()', function () {
      var leaveRequestID = '101';
      var params = {
        key: 'value'
      };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
        promise = LeaveRequestAPI.getComments(leaveRequestID, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint with leaveRequestID', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest',
            'getcomment', jasmine.objectContaining(_.assign(params, {
              leave_request_id: leaveRequestID
            })), false);
        });
      });

      it('returns data', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.getComments().values);
        });
      });
    });

    describe('saveComment()', function () {
      var commentObject = commentsData.getComments().values[0];
      var leaveRequestID = '102';
      var params = {
        key: 'value'
      };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.saveComment(leaveRequestID, commentObject, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint with leaveRequestID, text and contact_id', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest',
            'addcomment', jasmine.objectContaining(_.assign(params, {
              leave_request_id: leaveRequestID,
              text: commentObject.text,
              contact_id: commentObject.contact_id,
              created_at: commentObject.created_at
            })));
        });
      });

      it('returns data', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.addComment().values);
        });
      });
    });

    describe('deleteComment()', function () {
      var commentID = '101';
      var params = {
        key: 'value'
      };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.deleteComment(commentID, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint with comment_id', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest',
            'deletecomment', jasmine.objectContaining(_.assign(params, {
              comment_id: commentID
            })));
        });
      });

      it('returns data', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.deleteComment().values);
        });
      });
    });

    describe('getAttachments', function () {
      var leaveRequestID = '101';
      var params = {
        key: 'value'
      };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
        promise = LeaveRequestAPI.getAttachments(leaveRequestID, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the endpoint with leave request id', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest',
            'getattachments', jasmine.objectContaining(_.assign(params, {
              leave_request_id: leaveRequestID
            })), false);
        });
      });

      it('returns attachment data', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.getAttachments().values);
        });
      });
    });

    describe('deleteAttachment', function () {
      var leaveRequestID = '101';
      var attachmentID = '10';
      var params = {
        key: 'value'
      };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.deleteAttachment(leaveRequestID, attachmentID, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the endpoints with leave request id and attachment id', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest',
            'deleteattachment', jasmine.objectContaining(_.assign(params, {
              leave_request_id: leaveRequestID,
              attachment_id: attachmentID
            })));
        });
      });

      it('returns success data', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.deleteAttachment().values);
        });
      });
    });

    /**
     * Intercept HTTP calls to be handled by httpBackend
     **/
    function interceptHTTP () {
      // Intercept backend calls for LeaveRequest.all
      $httpBackend.whenGET(/action=getFull&entity=LeaveRequest/)
        .respond(mockData.all());

      // Intercept backend calls for LeaveRequest.balanceChangeByAbsenceType
      $httpBackend.whenGET(/action=getbalancechangebyabsencetype&entity=LeaveRequest/)
        .respond(mockData.balanceChangeByAbsenceType());

      // Intercept backend calls for LeaveRequest.getComments
      $httpBackend.whenGET(/action=getcomment&entity=LeaveRequest/)
        .respond(mockData.getComments());

      // Intercept backend calls for LeaveRequest.getAttachments
      $httpBackend.whenGET(/action=getattachments&entity=LeaveRequest/)
        .respond(mockData.getAttachments());

      // Intercept backend calls for LeaveRequest.create in POST
      $httpBackend.whenPOST(/\/civicrm\/ajax\/rest/)
        .respond(function (method, url, data, headers, params) {
          if (helper.isEntityActionInPost(data, 'LeaveRequest', 'create')) {
            return [201, mockData.all()];
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'calculatebalancechange')) {
            return [200, mockData.calculateBalanceChange()];
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'isValid')) {
            return [200, mockData.getisValid()];
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'deletecomment')) {
            return [200, mockData.deleteComment()];
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'addcomment')) {
            return [200, mockData.addComment()];
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'deleteattachment')) {
            return [200, mockData.deleteAttachment()];
          }
        });
    }
  });
});
