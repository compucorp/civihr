/* eslint-env amd, jasmine */

define([
  'common/angular',
  'mocks/data/leave-request-data',
  'leave-absences/manager-leave/app'
], function (angular, leaveRequestData) {
  'use strict';

  describe('leaveRequestPopupFilesTab', function () {
    var leaveRequest, $componentController, $log, $q, $rootScope, controller, OptionGroup, OptionGroupAPIMock,
      LeaveRequestInstance;

    beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'manager-leave'));

    beforeEach(inject(function (
      _$componentController_, _$q_, _$log_, _$rootScope_, _LeaveRequestInstance_,
      _OptionGroup_, _OptionGroupAPIMock_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
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

    describe('canUploadMore()', function () {
      describe('when number of files are below allowed limit', function () {
        beforeEach(function () {
          controller.request.files = [1, 2, 3, 4];
          controller.fileUploader.queue = [1, 2];
        });

        it('returns true', function () {
          expect(controller.canUploadMore()).toBeTruthy();
        });
      });

      describe('when number of files are above allowed limit', function () {
        beforeEach(function () {
          controller.request.files = [1, 2, 3, 4, 5];
          controller.fileUploader.queue = [1, 2, 3, 4, 5];
        });

        it('returns false', function () {
          expect(controller.canUploadMore()).toBeFalsy();
        });
      });
    });

    describe('uploadFiles: start', function () {
      beforeEach(function () {
        controller.fileUploader.queue = [1, 2, 3];
        controller.fileUploader.uploadAll = jasmine.createSpy('uploadAll');
        spyOn($rootScope, '$broadcast').and.callThrough();
      });

      describe('when upload is successful', function () {
        beforeEach(function () {
          controller.fileUploader.uploadAll.and.returnValue($q.resolve());
          $rootScope.$broadcast('uploadFiles: start');
          $rootScope.$digest();
        });

        it('uploads all files', function () {
          expect(controller.fileUploader.uploadAll).toHaveBeenCalledWith({ entityID: controller.request.id });
        });

        it('broadcasts success event', function () {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('uploadFiles: success');
        });
      });

      describe('when upload is not successful', function () {
        beforeEach(function () {
          controller.fileUploader.uploadAll.and.returnValue($q.reject());
          $rootScope.$broadcast('uploadFiles: start');
          $rootScope.$digest();
        });

        it('broadcasts error event', function () {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('uploadFiles: error');
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
