/* eslint-env amd, jasmine */
/* global CRM, inject */

define([
  'common/angular',
  'mocks/data/leave-request-data',
  'leave-absences/manager-leave/app'
], function (angular, leaveRequestData) {
  'use strict';

  describe('leaveRequestPopupFilesTab', function () {
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

    describe('when user uploads files', function () {
      describe('when number of files are below allowed limit', function () {
        beforeEach(function () {
          controller.request.files = [1, 2, 3, 4];
          controller.request.fileUploader.queue = [1, 2];
        });

        it('returns true', function () {
          expect(controller.canUploadMore()).toBeTruthy();
        });
      });

      describe('when number of files are above allowed limit', function () {
        beforeEach(function () {
          controller.request.files = [1, 2, 3, 4, 5];
          controller.request.fileUploader.queue = [1, 2, 3, 4, 5];
        });

        it('returns false', function () {
          expect(controller.canUploadMore()).toBeFalsy();
        });
      });
    });

    function compileComponent (canManage, request) {
      controller = $componentController('leaveRequestPopupFilesTab', null, {
        canManage: canManage,
        mode: 'edit',
        request: request
      });
      $rootScope.$digest();
    }
  });
});
