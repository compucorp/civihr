/* eslint-env amd, jasmine */

define([
  'common/angular',
  'leave-absences/mocks/data/leave-request.data',
  'leave-absences/manager-leave/app',
  'leave-absences/mocks/apis/option-group-api-mock'
], function (angular, leaveRequestData) {
  'use strict';

  describe('leaveRequestPopupFilesTab', function () {
    var leaveRequest, $componentController, $log, $rootScope, $scope, controller,
      OptionGroup, OptionGroupAPIMock, LeaveRequestInstance;

    beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'manager-leave'));

    beforeEach(inject(function (
      _$componentController_, _$log_, _$rootScope_, _LeaveRequestInstance_,
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

    it('emits a "add tab" event', function () {
      expect($scope.$emit).toHaveBeenCalledWith('LeaveRequestPopup::addTab', controller);
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
    });

    describe('can submit', function () {
      describe('when files have been queued for uploading', function () {
        beforeEach(function () {
          controller.fileUploader.queue = [1, 2, 3];
        });

        it('allows the request to be submitted', function () {
          expect(controller.canSubmit()).toBe(true);
        });
      });

      describe('when files have not been queued for uploading', function () {
        beforeEach(function () {
          controller.fileUploader.queue = [];
        });

        it('does not allow the request to be submitted', function () {
          expect(controller.canSubmit()).toBe(false);
        });
      });
    });

    function compileComponent (canManage, request) {
      $scope = $rootScope.$new();
      spyOn($scope, '$emit').and.callThrough();

      controller = $componentController('leaveRequestPopupFilesTab',
        { $scope: $scope },
        {
          canManage: canManage,
          mode: 'edit',
          request: request
        }
      );
      controller.$onInit();
      $rootScope.$digest();
    }
  });
});
