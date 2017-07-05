/* eslint-env amd, jasmine */
/* global CRM, inject */

define([
  'common/angular',
  'mocks/data/leave-request-data',
  'leave-absences/manager-leave/app'
], function (angular, leaveRequestData) {
  'use strict';

  describe('leaveRequestPopupCommentsTab', function () {
    var leaveRequest, $componentController, $log, $rootScope, controller, LeaveRequestInstance;

    beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'manager-leave'));

    beforeEach(inject(function (
      _$componentController_, _$q_, _$log_, _$rootScope_, _LeaveRequestInstance_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      LeaveRequestInstance = _LeaveRequestInstance_;

      spyOn($log, 'debug');

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
    });

    describe('addComment()', function () {
      beforeEach(function () {
        controller.request.comments = [];
        controller.request.contact_id = '101';
        controller.comment.text = 'some text';
        controller.request.id = '102';
        controller.addComment();
      });

      it('adds comment to the request', function () {
        expect(controller.request.comments.length).not.toBe(0);
      });

      it('adds comment with proper values', function () {
        expect(controller.request.comments[0]).toEqual({
          contact_id: '101',
          created_at: jasmine.any(String),
          leave_request_id: '102',
          text: 'some text'
        });
      });

      it('clears the comment text box', function () {
        expect(controller.comment.text).toBe('');
      });
    });

    describe('getCommentorName()', function () {
      var returnValue;

      describe('when comment author is same as logged in user', function () {
        beforeEach(function () {
          controller.request.contact_id = '101';
          returnValue = controller.getCommentorName('101');
        });

        it('returns "Me"', function () {
          expect(returnValue).toBe('Me');
        });
      });

      describe('when comment author is not same as logged in user', function () {
        var displayName = 'MR X';

        beforeEach(function () {
          controller.request.contact_id = '101';
          controller.comment.contacts = {
            102: {
              display_name: displayName
            }
          };
          returnValue = controller.getCommentorName('102');
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
          comment.comment_id = jasmine.any(String);

          compileComponent(true, leaveRequest);

          returnValue = controller.removeCommentVisibility(comment);
        });

        it('button should be visible', function () {
          expect(returnValue).toBe(true);
        });
      });

      describe('when comment id is not missing and role is neither manager nor admin', function () {
        beforeEach(function () {
          comment.comment_id = jasmine.any(String);

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
