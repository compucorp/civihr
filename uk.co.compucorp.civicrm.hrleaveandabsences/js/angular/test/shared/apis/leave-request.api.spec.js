/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'mocks/data/leave-request-data',
  'mocks/data/sickness-leave-request-data',
  'mocks/data/toil-leave-request-data',
  'mocks/data/comments-data',
  'mocks/helpers/helper',
  'leave-absences/shared/apis/leave-request.api',
  'leave-absences/shared/modules/shared-settings'
], function (_, moment, mockData, sicknessMockData, toilMockData, commentsData, helper) {
  'use strict';

  describe('LeaveRequestAPI', function () {
    var LeaveRequestAPI, $httpBackend, $rootScope, $q, promise;
    var balanceChangeBreakdownMock = mockData.balanceChangeBreakdown();

    beforeEach(module('leave-absences.apis', 'leave-absences.settings'));
    beforeEach(inject(['$httpBackend', '$q', '$rootScope', 'LeaveRequestAPI',
      function (_$httpBackend_, _$q_, _$rootScope_, _LeaveRequestAPI_) {
        $httpBackend = _$httpBackend_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        LeaveRequestAPI = _LeaveRequestAPI_;

        interceptHTTP();
      }
    ]));

    afterEach(function () {
      $rootScope.$digest();
    });

    describe('all()', function () {
      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
      });

      describe('with a standard default request', function () {
        beforeEach(function () {
          promise = LeaveRequestAPI.all();
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls the LeaveRequest.getFull endpoint', function () {
          expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[0]).toBe('LeaveRequest');
          expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[1]).toBe('getFull');
        });

        it('returns all the data', function () {
          promise.then(function (response) {
            expect(response.list).toEqual(mockData.all().values);
          });
        });
      });

      describe('when the request has an empty list of contact to use as filter', function () {
        beforeEach(function () {
          promise = LeaveRequestAPI.all({ contact_id: { IN: [] } });
        });

        it('does not call the API', function () {
          expect(LeaveRequestAPI.sendGET).not.toHaveBeenCalled();
        });

        it('returns directly an empty list', function () {
          promise.then(function (response) {
            expect(response).toEqual({ list: [], total: 0, allIds: [] });
          });
        });
      });
    });

    describe('balanceChangeByAbsenceType()', function () {
      describe('without errors from server', function () {
        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
        });

        describe('basic tests', function () {
          var statuses = [jasmine.any(String), jasmine.any(String), jasmine.any(String)];

          beforeEach(function () {
            promise = LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String), statuses, true);
          });

          afterEach(function () {
            $httpBackend.flush();
          });

          it('calls the LeaveRequest.getbalancechangebyabsencetype endpoint', function () {
            promise.then(function () {
              expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[0]).toBe('LeaveRequest');
              expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[1]).toBe('getbalancechangebyabsencetype');
            });
          });

          it('sends as `statuses` an "IN" parameter', function () {
            expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
              statuses: { 'IN': statuses }
            }));
          });

          it('returns the api data as is', function () {
            promise.then(function (response) {
              expect(response).toEqual(mockData.balanceChangeByAbsenceType().values);
            });
          });
        });

        describe('default values', function () {
          afterEach(function () {
            $httpBackend.flush();
          });

          describe('when passing falsy values for status and publicHolidays', function () {
            beforeEach(function () {
              LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), jasmine.any(String));
            });

            it('assigns default values to them', function () {
              expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
                statuses: null,
                public_holiday: false
              }));
            });
          });
        });

        describe('error handling', function () {
          it('throws error if contact_id is blank', function () {
            LeaveRequestAPI.balanceChangeByAbsenceType(null, jasmine.any(String))
              .catch(commonExpect);
          });

          it('throws error if periodId is blank', function () {
            LeaveRequestAPI.balanceChangeByAbsenceType(jasmine.any(String), null)
              .catch(commonExpect);
          });

          function commonExpect (data) {
            expect(data).toBe('contact_id and period_id are mandatory');
          }
        });
      });
    });

    describe('calculateBalanceChange()', function () {
      describe('basic tests', function () {
        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
          promise = LeaveRequestAPI.calculateBalanceChange(helper.createRandomLeaveRequest());
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls the LeaveRequest.calculatebalancechange endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[0]).toBe('LeaveRequest');
            expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[1]).toBe('calculatebalancechange');
          });
        });

        it('returns the api data as is', function () {
          promise.then(function (response) {
            expect(response).toEqual(mockData.calculateBalanceChange().values);
          });
        });
      });

      describe('error handling', function () {
        beforeEach(function () {
          promise = LeaveRequestAPI.calculateBalanceChange({});
        });

        it('throws an error if contact_id, from_date, or from_date_type are missing', function () {
          promise.catch(function (result) {
            expect(result).toBe('contact_id, from_date and from_date_type in params are mandatory');
          });
        });
      });
    });

    describe('getBalanceChangeBreakdown()', function () {
      describe('basic tests', function () {
        var leaveRequestId = 1;

        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
          promise = LeaveRequestAPI.getBalanceChangeBreakdown(leaveRequestId);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls the LeaveRequest.getBreakdown endpoint', function () {
          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith(
            'LeaveRequest', 'getBreakdown', { leave_request_id: leaveRequestId }, false);
        });

        it('returns the api data as is', function () {
          promise.then(function (result) {
            expect(result).toEqual(balanceChangeBreakdownMock);
          });
        });
      });
    });

    describe('create()', function () {
      var requestData;

      describe('basic tests', function () {
        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();

          requestData = helper.createRandomLeaveRequest();
          promise = LeaveRequestAPI.create(requestData);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls the LeaveRequest.create endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'create', requestData);
          });
        });
      });

      describe('error handling', function () {
        describe('when one of the mandatory fields are missing', function () {
          beforeEach(function () {
            requestData = helper.createRandomLeaveRequest();
            delete requestData.contact_id;

            promise = LeaveRequestAPI.create(requestData);
          });

          it('rejects the promise', function () {
            promise.catch(function (result) {
              expect(result).toBe('contact_id, from_date, status_id and from_date_type params are mandatory');
            });
          });
        });

        describe('when the "to date" is present, but "to date type" is not', function () {
          beforeEach(function () {
            requestData = helper.createRandomLeaveRequest();
            delete requestData.to_date_type;

            promise = LeaveRequestAPI.create(requestData);
          });

          it('rejects the promise', function () {
            promise.catch(function (result) {
              expect(result).toBe('to_date_type is mandatory');
            });
          });
        });
      });
    });

    describe('delete()', function () {
      var idToDelete = '123';

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST');
        LeaveRequestAPI.delete(idToDelete);
      });

      it('calls the LeaveRequest.delete endpoint', function () {
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[1]).toBe('delete');
      });

      it('passes the leave request id to the endpoint', function () {
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[2]).toEqual({
          id: idToDelete
        });
      });
    });

    describe('isValid()', function () {
      describe('basic tests', function () {
        var requestData;

        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();

          requestData = helper.createRandomSicknessRequest();
          promise = LeaveRequestAPI.isValid(requestData);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls the LeaveRequest.isValid endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'isValid', requestData);
          });
        });
      });

      describe('when the leave request is not valid', function () {
        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'sendPOST').and.callFake(function (params) {
            return $q.resolve(mockData.getNotIsValid());
          });
          promise = LeaveRequestAPI.isValid(helper.createRandomSicknessRequest());
        });

        it('rejects the promise with a flatten list of validation errors', function () {
          var errors = _(mockData.getNotIsValid().values).map().flatten().value();

          promise.catch(function (result) {
            expect(result).toEqual(errors);
          });
        });
      });
    });

    describe('update()', function () {
      var requestData;

      beforeEach(function () {
        // temp fix, if we use the mock data directly we end up
        // changing the original object and subsequent tests relying on it
        // will fail
        requestData = _.assign({}, mockData.all().values[0]);
      });

      describe('basic tests', function () {
        beforeEach(function () {
          spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
          promise = LeaveRequestAPI.update(requestData);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls the LeaveRequest.create endpoint', function () {
          promise.then(function () {
            expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest', 'create', requestData);
          });
        });

        it('returns the updated leave request', function () {
          promise.then(function (result) {
            expect(result.id).toBeDefined();
          });
        });
      });

      describe('error handling', function () {
        describe('when the leave request id is not set', function () {
          beforeEach(function () {
            delete requestData.id;
            promise = LeaveRequestAPI.update(requestData);
          });

          it('rejects the promise', function () {
            promise.catch(function (result) {
              expect(result).toBe('id is mandatory field');
            });
          });
        });
      });
    });

    describe('getComments()', function () {
      var leaveRequestId = '101';
      var params = { key: 'value' };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
        promise = LeaveRequestAPI.getComments(leaveRequestId, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the LeaveRequest.getcomment endpoint', function () {
        expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[1]).toBe('getcomment');
      });

      it('passes to the endpoint the leave request id merged with the other params', function () {
        expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[2]).toEqual(_.assign({}, params, {
          leave_request_id: leaveRequestId
        }));
      });

      it('returns the api data as is', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.getComments().values);
        });
      });
    });

    describe('saveComment()', function () {
      var commentObject = commentsData.getComments().values[0];
      var leaveRequestID = '102';
      var params = { key: 'value' };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.saveComment(leaveRequestID, commentObject, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the LeaveRequest.addcomment endpoint', function () {
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[1]).toBe('addcomment');
      });

      it('passes to the endpoint the leave request id and the comment data mixed with the other params', function () {
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[2]).toEqual(_.assign({}, params, {
          leave_request_id: leaveRequestID,
          text: commentObject.text,
          contact_id: commentObject.contact_id
        }));
      });

      it('returns the api data as is', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.addComment().values);
        });
      });
    });

    describe('deleteComment()', function () {
      var commentID = '101';
      var params = { key: 'value' };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.deleteComment(commentID, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the LeaveRequest.deletecomment endpoint', function () {
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[1]).toBe('deletecomment');
      });

      it('passes to the endpoint the comment id mixed with the other params', function () {
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[2]).toEqual(_.assign({}, params, {
          comment_id: commentID
        }));
      });

      it('returns the data as is', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.deleteComment().values);
        });
      });
    });

    describe('getAttachments', function () {
      var leaveRequestID = '101';
      var params = { key: 'value' };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
        promise = LeaveRequestAPI.getAttachments(leaveRequestID, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the LeaveRequest.getattachments endpoint', function () {
        expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[1]).toBe('getattachments');
      });

      it('passes to the endpoint the leave request id mixed with the other params', function () {
        expect(LeaveRequestAPI.sendGET.calls.mostRecent().args[2]).toEqual(_.assign({}, params, {
          leave_request_id: leaveRequestID
        }));
      });

      it('returns the api data as is', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.getAttachments().values);
        });
      });
    });

    describe('deleteAttachment', function () {
      var leaveRequestID = '101';
      var attachmentID = '10';
      var params = { key: 'value' };

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.deleteAttachment(leaveRequestID, attachmentID, params);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the LeaveRequest.deleteattachment endpoint', function () {
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[0]).toBe('LeaveRequest');
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[1]).toBe('deleteattachment');
      });

      it('passes to the endpoint the leave request id and attachment id mixed with the other params', function () {
        expect(LeaveRequestAPI.sendPOST.calls.mostRecent().args[2]).toEqual(_.assign({}, params, {
          leave_request_id: leaveRequestID,
          attachment_id: attachmentID
        }));
      });

      it('returns the api data as is', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.deleteAttachment().values);
        });
      });
    });

    describe('find()', function () {
      var id = '123';

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendGET').and.callThrough();
        promise = LeaveRequestAPI.find(id);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls the LeaveRequest.get endpoint', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendGET).toHaveBeenCalledWith('LeaveRequest', 'getFull', { id: id });
        });
      });
    });

    describe('isManagedBy()', function () {
      var leaveRequestID = '101';
      var contactID = '102';

      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'sendPOST').and.callThrough();
        promise = LeaveRequestAPI.isManagedBy(leaveRequestID, contactID);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('calls endpoint with leaveRequestID and contactID', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.sendPOST).toHaveBeenCalledWith('LeaveRequest',
            'isManagedBy', jasmine.objectContaining({
              leave_request_id: leaveRequestID,
              contact_id: contactID
            }));
        });
      });

      it('returns data', function () {
        promise.then(function (result) {
          expect(result).toEqual(mockData.isManagedBy().values);
        });
      });
    });

    /**
     * Intercept HTTP calls to be handled by httpBackend
     */
    function interceptHTTP () {
      // Intercept backend calls for LeaveRequest.all
      $httpBackend.whenGET(/action=getFull&entity=LeaveRequest/)
        .respond(mockData.all());

      // Intercept backend calls for LeaveRequest.all
      $httpBackend.whenGET(/action=get&entity=LeaveRequest/)
        .respond(mockData.singleDataSuccess());

      // Intercept backend calls for LeaveRequest.balanceChangeByAbsenceType
      $httpBackend.whenGET(/action=getbalancechangebyabsencetype&entity=LeaveRequest/)
        .respond(mockData.balanceChangeByAbsenceType());

      // Intercept backend calls for LeaveRequest.getComments
      $httpBackend.whenGET(/action=getcomment&entity=LeaveRequest/)
        .respond(mockData.getComments());

      // Intercept backend calls for LeaveRequest.getAttachments
      $httpBackend.whenGET(/action=getattachments&entity=LeaveRequest/)
        .respond(mockData.getAttachments());

      // Intercept backend calls for LeaveRequest.getBreakdown
      $httpBackend.whenGET(/action=getBreakdown&entity=LeaveRequest/)
        .respond(balanceChangeBreakdownMock);

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
          } else if (helper.isEntityActionInPost(data, 'LeaveRequest', 'isManagedBy')) {
            return [200, mockData.isManagedBy()];
          }
        });
    }
  });
});
