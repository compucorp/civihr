/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/leave-request-data',
  'common/mocks/models/instances/session-mock',
  'leave-absences/manager-leave/app'
], function (angular, leaveRequestData) {
  'use strict';

  describe('leaveRequestPopupCommentsTab', function () {
    var $componentController, $provide, $log, $rootScope, controller,
      leaveRequest, LeaveRequestInstance, OptionGroup, OptionGroupAPIMock,
      SessionMock;
    var managerId = '102';
    var requestId = '100';
    var contactId = '101';
    var commentId = '12';

    beforeEach(module('common.mocks', 'leave-absences.templates',
    'leave-absences.mocks', 'manager-leave', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_SessionMock_) {
      SessionMock = _SessionMock_;
      SessionMock.sessionObject.contactId = contactId;
      $provide.value('Session', SessionMock);
    }));

    beforeEach(inject(function (
      _$componentController_, _$q_, _$log_, _$rootScope_, _LeaveRequestInstance_,
      _OptionGroup_, _OptionGroupAPIMock_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      LeaveRequestInstance = _LeaveRequestInstance_;
      OptionGroupAPIMock = _OptionGroupAPIMock_;
      OptionGroup = _OptionGroup_;

      spyOn($log, 'debug');

      spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
        return OptionGroupAPIMock.valuesOf(name);
      });
      leaveRequest = LeaveRequestInstance.init(leaveRequestData.singleDataSuccess());
      compileComponent(false, leaveRequest);
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('on init', function () {
      describe('comments', function () {
        it('text is empty', function () {
          expect(controller.comment.text).toBe('');
        });

        it('contacts is not loaded', function () {
          expect(controller.comment.contacts).toEqual({});
        });
      });

      describe('when loading `currently logged in user` is done', function () {
        it('stops loading the component', function () {
          expect(controller.loading.component).toBe(false);
        });
      });
    });

    describe('addComment()', function () {
      var commentText = 'some text';

      beforeEach(function () {
        controller.request.contact_id = contactId;
        controller.request.comments = [];
        controller.comment.text = commentText;
        controller.request.id = leaveRequest.id;

        controller.addComment();
      });

      it('adds comment to the request', function () {
        expect(controller.request.comments.length).not.toBe(0);
      });

      it('adds comment with proper values', function () {
        expect(controller.request.comments[0]).toEqual({
          contact_id: contactId,
          leave_request_id: leaveRequest.id,
          text: commentText
        });
      });

      it('clears the comment text box', function () {
        expect(controller.comment.text).toBe('');
      });

      describe('when a manager adds a comment', function () {
        var managerComment = 'another text';

        beforeEach(function () {
          SessionMock.sessionObject.contactId = managerId;
          leaveRequest = LeaveRequestInstance.init(leaveRequestData.singleDataSuccess());
          compileComponent(true, leaveRequest);

          controller.canManage = true;
          controller.request.comments = [];
          controller.request.id = requestId;
          controller.comment.text = managerComment;

          controller.addComment();
        });

        it('adds the manager comment to the request', function () {
          expect(controller.request.comments[0]).toEqual({
            contact_id: managerId,
            leave_request_id: requestId,
            text: managerComment
          });
        });
      });
    });

    describe('getCommentorName()', function () {
      var returnValue;

      describe('when comment author is same as logged in user', function () {
        beforeEach(function () {
          controller.request.contact_id = contactId;
          returnValue = controller.getCommentorName(contactId);
        });

        it('returns "Me"', function () {
          expect(returnValue).toBe('Me');
        });
      });

      describe('when comment author is not same as logged in user', function () {
        var displayName = 'Mr user';
        var commentatorId = '102';

        beforeEach(function () {
          controller.request.contact_id = contactId;
          controller.comment.contacts = {};
          controller.comment.contacts[commentatorId] = { display_name: displayName };
          returnValue = controller.getCommentorName(commentatorId);
        });

        it('returns name of the comment author', function () {
          expect(returnValue).toBe(displayName);
        });
      });
    });

    describe('removeCommentVisibility()', function () {
      var returnValue;
      var comment = {};

      describe('when comment id is missing and role is either manager or admin', function () {
        beforeEach(function () {
          comment.comment_id = null;

          compileComponent(true, leaveRequest);

          returnValue = controller.removeCommentVisibility(comment);
        });

        it('button should be visible', function () {
          expect(returnValue).toBe(true);
        });
      });

      describe('when comment id is not missing and role is either manager or admin', function () {
        beforeEach(function () {
          comment.comment_id = commentId;

          compileComponent(true, leaveRequest);

          returnValue = controller.removeCommentVisibility(comment);
        });

        it('button should be visible', function () {
          expect(returnValue).toBe(true);
        });
      });

      describe('when comment id is not missing and role is neither manager nor admin', function () {
        beforeEach(function () {
          comment.comment_id = commentId;

          compileComponent(false, leaveRequest);

          returnValue = controller.removeCommentVisibility(comment);
        });

        it('button should not be visible', function () {
          expect(returnValue).toBe(false);
        });
      });

      describe('when comment id is missing and role is neither manager nor admin', function () {
        beforeEach(function () {
          comment.comment_id = null;

          compileComponent(false, leaveRequest);

          returnValue = controller.removeCommentVisibility(comment);
        });

        it('button should be visible', function () {
          expect(returnValue).toBe(true);
        });
      });
    });

    function compileComponent (canManage, request) {
      controller = $componentController('leaveRequestPopupCommentsTab', null, {
        canManage: canManage,
        mode: 'edit',
        request: request
      });
      $rootScope.$digest();
    }
  });
});
