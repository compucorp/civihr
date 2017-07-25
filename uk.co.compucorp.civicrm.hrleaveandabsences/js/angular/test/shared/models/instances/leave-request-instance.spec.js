/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/leave-request-data',
  'mocks/data/option-group-mock-data',
  'mocks/data/comments-data',
  'mocks/helpers/helper',
  'common/mocks/services/file-uploader-mock',
  'mocks/apis/leave-request-api-mock',
  'leave-absences/shared/models/instances/leave-request-instance',
  'leave-absences/shared/modules/models'
], function (_, leaveRequestMockData, optionGroupMockData, commentsData, helper) {
  'use strict';

  describe('LeaveRequestInstance', function () {
    var $provide, LeaveRequestInstance, LeaveRequestAPI, $q, OptionGroup, OptionGroupAPIMock,
      $rootScope, instance, sharedSettings;

    beforeEach(module('leave-absences.models', 'leave-absences.models.instances',
      'leave-absences.mocks', 'common.mocks', 'leave-absences.settings',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_LeaveRequestAPIMock_, _FileUploaderMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('FileUploader', _FileUploaderMock_);
    }));

    afterEach(function () {
      $rootScope.$apply();
    });

    beforeEach(inject(['LeaveRequestInstance', 'LeaveRequestAPI', '$rootScope',
      '$q', 'shared-settings', 'OptionGroup', 'OptionGroupAPIMock',
      function (_LeaveRequestInstance_, _LeaveRequestAPI_, _$rootScope_, _$q_, _sharedSettings_, _OptionGroup_, _OptionGroupAPIMock_) {
        LeaveRequestAPI = _LeaveRequestAPI_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        sharedSettings = _sharedSettings_;
        OptionGroup = _OptionGroup_;
        OptionGroupAPIMock = _OptionGroupAPIMock_;

        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });

        LeaveRequestInstance = _LeaveRequestInstance_.init({}, false);

        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
        spyOn(LeaveRequestAPI, 'saveComment').and.callThrough();
        spyOn(LeaveRequestAPI, 'getComments').and.callThrough();
        spyOn(LeaveRequestAPI, 'deleteComment').and.callThrough();
        spyOn(LeaveRequestAPI, 'getAttachments').and.callThrough();
        spyOn(LeaveRequestAPI, 'deleteAttachment').and.callThrough();
      }
    ]));

    describe('default values', function () {
      var instance;

      beforeEach(function () {
        instance = LeaveRequestInstance.init({});
      });

      it('has an empty list of comments', function () {
        expect(instance.comments.length).toBe(0);
      });

      it('sets the request type to "leave"', function () {
        expect(instance.request_type).toEqual('leave');
      });
    });

    describe('status change methods', function () {
      var instance, promise;

      describe('cancel()', function () {
        beforeEach(function () {
          commonSetup(sharedSettings.statusNames.cancelled, 'cancel');
        });

        it('fetches the hrleaveandabsences_leave_request_status Option Values', function () {
          expect(OptionGroup.valuesOf).toHaveBeenCalledWith('hrleaveandabsences_leave_request_status');
        });

        it('internally calls the update() method', function () {
          promise.then(function () {
            expect(instance.update).toHaveBeenCalled();
          });
        });

        it('updates the status_id of the instance', function () {
          promise.then(function () {
            expect(instance.status_id).toBe(getStatusIdByName(sharedSettings.statusNames.cancelled));
          });
        });
      });

      describe('approve()', function () {
        beforeEach(function () {
          commonSetup(sharedSettings.statusNames.approved, 'approve');
        });

        it('fetches the hrleaveandabsences_leave_request_status Option Values', function () {
          promise.then(function () {
            expect(OptionGroup.valuesOf).toHaveBeenCalledWith('hrleaveandabsences_leave_request_status');
          });
        });

        it('updates the status_id of the instance', function () {
          promise.then(function () {
            expect(instance.status_id).toBe(getStatusIdByName(sharedSettings.statusNames.approved));
          });
        });

        it('internally calls the update() method', function () {
          promise.then(function () {
            expect(instance.update).toHaveBeenCalled();
          });
        });
      });

      describe('reject()', function () {
        beforeEach(function () {
          commonSetup(sharedSettings.statusNames.rejected, 'reject');
        });

        it('fetches the hrleaveandabsences_leave_request_status Option Values', function () {
          promise.then(function () {
            expect(OptionGroup.valuesOf).toHaveBeenCalledWith('hrleaveandabsences_leave_request_status');
          });
        });

        it('internally calls the update() method', function () {
          promise.then(function () {
            expect(instance.update).toHaveBeenCalled();
          });
        });

        it('updates the status_id of the instance', function () {
          promise.then(function () {
            expect(instance.status_id).toBe(getStatusIdByName(sharedSettings.statusNames.rejected));
          });
        });
      });

      describe('sendBack()', function () {
        beforeEach(function () {
          commonSetup(sharedSettings.statusNames.moreInformationRequired, 'sendBack');
        });

        it('fetches the hrleaveandabsences_leave_request_status Option Values', function () {
          promise.then(function () {
            expect(OptionGroup.valuesOf).toHaveBeenCalledWith('hrleaveandabsences_leave_request_status');
          });
        });

        it('internally calls the update() method', function () {
          promise.then(function () {
            expect(instance.update).toHaveBeenCalled();
          });
        });

        it('updates the status_id of the instance', function () {
          promise.then(function () {
            expect(instance.status_id).toBe(getStatusIdByName(sharedSettings.statusNames.moreInformationRequired));
          });
        });
      });

      function commonSetup (statusName, methodName) {
        // spyOn(OptionGroup, 'valuesOf').and.returnValue($q.resolve([{
        //   name: statusName, value: getStatusIdByName(statusName)
        // }]));

        instance = LeaveRequestInstance.init(helper.createRandomLeaveRequest());
        promise = instance[methodName]();
      }

      function getStatusIdByName (statusName) {
        return optionGroupMockData.getCollection('hrleaveandabsences_leave_request_status').find(function (option) {
          return option.name === statusName;
        }).value;
      }
    });

    describe('update()', function () {
      var promise, instance;

      beforeEach(function () {
        instance = LeaveRequestInstance.init(helper.createRandomLeaveRequest());
        instance.comments = commentsData.getCommentsWithMixedIDs().values;
        instance.comments.push(_.assign(commentsData.getComments().values[0], { toBeDeleted: true }));
        instance.fileUploader = { queue: [{ 'key': 2 }] };

        LeaveRequestAPI.update.and.returnValue($q.resolve());
        spyOn(instance, 'toAPI');
        instance.fileUploader.uploadAll = jasmine.createSpy('uploadAll');

        promise = instance.update();
      });

      it('prepares the attributes to be sent to the api', function () {
        promise.then(function () {
          expect(instance.toAPI).toHaveBeenCalled();
        });
      });

      it('sends the update request', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.update).toHaveBeenCalledWith(instance.toAPI());
        });
      });

      describe('after the update is done', function () {
        describe('comments', function () {
          it('saves only the newly created comments', function () {
            promise.then(function () {
              commentsData.getCommentsWithMixedIDs().values.map(function (comment) {
                if (comment.comment_id) {
                  expect(LeaveRequestAPI.saveComment).not.toHaveBeenCalledWith(instance.id, comment);
                } else {
                  expect(LeaveRequestAPI.saveComment).toHaveBeenCalledWith(instance.id, comment);
                }
              });
            });
          });

          it('deletes the comments marked for deletion', function () {
            promise.then(function () {
              instance.comments.map(function (comment) {
                if (comment.toBeDeleted) {
                  expect(LeaveRequestAPI.deleteComment).toHaveBeenCalledWith(comment.comment_id);
                } else {
                  expect(LeaveRequestAPI.deleteComment).not.toHaveBeenCalledWith(comment.comment_id);
                }
              });
            });
          });
        });
      });
    });

    describe('create()', function () {
      var promise;

      beforeEach(function () {
        instance = LeaveRequestInstance.init(helper.createRandomLeaveRequest());
        instance.fileUploader = { queue: [{ 'key': 2 }] };
        instance.comments = commentsData.getCommentsWithMixedIDs().values;
        instance.comments.push((function () {
          return _.assign({}, commentsData.getComments().values[0], { toBeDeleted: true });
        }()));

        instance.fileUploader.uploadAll = jasmine.createSpy('uploadAll');

        promise = instance.create();
      });

      it('calls the equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.create).toHaveBeenCalled();
        });
      });

      it('adds the id property to the instance', function () {
        expect(instance.id).not.toBeDefined();

        promise.then(function () {
          expect(instance.id).toBeDefined();
          expect(instance.id).toEqual(jasmine.any(String));
        });
      });

      describe('after the creation is done', function () {
        describe('comments', function () {
          it('saves only the newly created comments', function () {
            promise.then(function () {
              commentsData.getCommentsWithMixedIDs().values.map(function (comment) {
                if (comment.comment_id) {
                  expect(LeaveRequestAPI.saveComment).not.toHaveBeenCalledWith(instance.id, comment);
                } else {
                  expect(LeaveRequestAPI.saveComment).toHaveBeenCalledWith(instance.id, comment);
                }
              });
            });
          });

          it('deletes the comments marked for deletion', function () {
            promise.then(function () {
              instance.comments.map(function (comment) {
                if (comment.toBeDeleted) {
                  expect(LeaveRequestAPI.deleteComment).toHaveBeenCalledWith(comment.comment_id);
                } else {
                  expect(LeaveRequestAPI.deleteComment).not.toHaveBeenCalledWith(comment.comment_id);
                }
              });
            });
          });
        });
      });

      describe('error handling', function () {
        describe('when one mandatory filed is missing', function () {
          beforeEach(function () {
            delete instance.contact_id;
            promise = instance.create();
          });

          it('fails to create instance', function () {
            promise.catch(function (error) {
              expect(error).toBe('contact_id, from_date and from_date_type in params are mandatory');
            });
          });
        });
      });
    });

    describe('loadComments()', function () {
      var instance, promise;

      beforeEach(function () {
        instance = LeaveRequestInstance.init(helper.createRandomLeaveRequest());
        instance.id = 18;

        spyOn(instance, 'loadComments').and.callThrough();

        promise = instance.loadComments();
      });

      it('calls API with leave request ID', function () {
        expect(LeaveRequestAPI.getComments).toHaveBeenCalledWith(instance.id);
      });

      it('stores internally the comments returned by the API', function () {
        promise.then(function () {
          expect(instance.comments).toEqual(commentsData.getComments().values);
        });
      });
    });

    describe('isValid()', function () {
      var instance;

      beforeEach(function () {
        instance = LeaveRequestInstance.init(helper.createRandomLeaveRequest());
        instance.isValid();
      });

      it('calls equivalent API method', function () {
        expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
      });
    });

    describe('check status methods', function () {
      var instance;

      beforeEach(function () {
        // spyOn(OptionGroup, 'valuesOf').and.callFake(function () {
        //   return $q.resolve(optionGroupMockData.getCollection('hrleaveandabsences_leave_request_status'));
        // });

        instance = LeaveRequestInstance.init(helper.createRandomLeaveRequest());
      });

      describe('isApproved()', function () {
        describe('when the request is approved', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.approved);
          });

          it('returns true', function () {
            instance.isApproved().then(function (data) {
              expect(data).toBe(true);
            });
          });
        });

        describe('when the request is not approved', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.cancelled);
          });

          it('returns false', function () {
            instance.isApproved().then(function (data) {
              expect(data).toBe(false);
            });
          });
        });
      });

      describe('isAwaitingApproval()', function () {
        describe('when the request is awaiting approval', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.awaitingApproval);
          });

          it('returns true', function () {
            instance.isAwaitingApproval().then(function (data) {
              expect(data).toBe(true);
            });
          });
        });

        describe('when the request is not awaiting approval', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.cancelled);
          });

          it('returns false', function () {
            instance.isAwaitingApproval().then(function (data) {
              expect(data).toBe(false);
            });
          });
        });
      });

      describe('isCancelled()', function () {
        describe('when the request is cancelled', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.cancelled);
          });

          it('returns true', function () {
            instance.isCancelled().then(function (data) {
              expect(data).toBe(true);
            });
          });
        });

        describe('status is not cancelled', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.approved);
          });

          it('returns false', function () {
            instance.isCancelled().then(function (data) {
              expect(data).toBe(false);
            });
          });
        });
      });

      describe('isRejected()', function () {
        describe('when the request is rejected', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.rejected);
          });

          it('returns true', function () {
            instance.isRejected().then(function (data) {
              expect(data).toBe(true);
            });
          });
        });

        describe('when the request is not rejected', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.approved);
          });

          it('returns false', function () {
            instance.isRejected().then(function (data) {
              expect(data).toBe(false);
            });
          });
        });
      });

      describe('isSentBack()', function () {
        describe('when the request is sent back for more information', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.moreInformationRequired);
          });

          it('returns true', function () {
            instance.isSentBack().then(function (data) {
              expect(data).toBe(true);
            });
          });
        });

        describe('when the request is not sent back for more information', function () {
          beforeEach(function () {
            instance.status_id = getStatusIdByName(sharedSettings.statusNames.approved);
          });

          it('returns false', function () {
            instance.isSentBack().then(function (data) {
              expect(data).toBe(false);
            });
          });
        });
      });

      function getStatusIdByName (statusName) {
        return optionGroupMockData.getCollection('hrleaveandabsences_leave_request_status').find(function (option) {
          return option.name === statusName;
        }).value;
      }
    });

    describe('toAPI()', function () {
      var instance;

      beforeEach(function () {
        instance = LeaveRequestInstance.init(leaveRequestMockData.all().values[1], true);

        // adding some custom properties
        instance.balance_change = _.random(-10, -5);
        instance.dates = jasmine.any(Array);
      });

      it('filters out custom properties on leave request instance', function () {
        expect(Object.keys(instance.toAPI())).toEqual(_.without(
          Object.keys(instance.attributes()),
          'balance_change',
          'dates',
          'comments',
          'fileUploader',
          'files'
        ));
      });
    });

    describe('attachments', function () {
      var attachments, instance, promise;

      beforeEach(function () {
        instance = LeaveRequestInstance.init(helper.createRandomLeaveRequest());
        instance.id = '12';

        attachments = leaveRequestMockData.getAttachments().values;
        promise = instance.loadAttachments();
      });

      describe('loadAttachments()', function () {
        it('fetches its own attachments', function () {
          expect(LeaveRequestAPI.getAttachments).toHaveBeenCalledWith(instance.id);
        });

        it('stores the attachments internally', function () {
          promise.then(function () {
            expect(instance.files.length).toEqual(attachments.length);
          });
        });
      });

      describe('deleteAttachment()', function () {
        beforeEach(function () {
          $rootScope.$apply();
          instance.deleteAttachment(attachments[0]);
        });

        it('flags the given attachment to be deleted', function () {
          instance.files.forEach(function (file) {
            if (file.attachment_id === attachments[0].attachment_id) {
              expect(file.toBeDeleted).toBe(true);
            } else {
              expect(file.toBeDeleted).toBeFalsy();
            }
          });
        });
      });

      describe('deleting attachments on upload', function () {
        var deletePromise;

        beforeEach(function () {
          deletePromise = promise.then(function () {
            instance.deleteAttachment(attachments[0]);
            return instance.update();
          });
        });

        it('sends the cancellation request to the API for each file marked to be deleted', function () {
          deletePromise.then(function (resolvedPromises) {
            _.each(instance.files, function (file) {
              if (file.toBeDeleted) {
                expect(LeaveRequestAPI.deleteAttachment).toHaveBeenCalledWith(instance.id, file.attachment_id);
              }
            });
          });
        });
      });
    });

    describe('deleteComment()', function () {
      var instance;
      var comment = { created_at: '2017-06-14 12:15:18', text: 'test comment' };

      beforeEach(function () {
        instance = LeaveRequestInstance.init(helper.createRandomLeaveRequest());
      });

      describe('when the comment is not yet stored in the DB', function () {
        beforeEach(function () {
          instance.comments = [comment];
          instance.deleteComment(comment);
        });

        it('removes the comment directly', function () {
          expect(instance.comments.length).toBe(0);
        });
      });

      describe('when the comment is already stored in the DB', function () {
        beforeEach(function () {
          comment.comment_id = '1';

          instance.comments = [comment];
          instance.deleteComment(comment);
        });

        it('marks the comment for deletion', function () {
          expect(comment.toBeDeleted).toBe(true);
        });
      });
    });

    describe('delete()', function () {
      beforeEach(function () {
        spyOn(LeaveRequestAPI, 'delete');

        instance = LeaveRequestInstance.init({ id: '123' });
        instance.delete();
      });

      it('calls the api delete endpoint, passing its own id', function () {
        expect(LeaveRequestAPI.delete).toHaveBeenCalledWith(instance.id);
      });
    });
  });
});
