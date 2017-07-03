/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function (angular) {
    'use strict';

    describe('myLeaveContainer', function () {
      var $componentController, $log, $rootScope, controller, $uibModal;

      beforeEach(module('leave-absences.templates', 'my-leave'));
      beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_, _$uibModal_) {
        $componentController = _$componentController_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        $uibModal = _$uibModal_;
        spyOn($log, 'debug');

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('leaveRequest', function () {
        it('DatePickerFrom is hidden', function () {
          expect(controller.leaveRequest.showDatePickerFrom).toBe(false);
        });

        it('DatePickerTo is hidden', function () {
          expect(controller.leaveRequest.showDatePickerTo).toBe(false);
        });

        it('change is not expanded', function () {
          expect(controller.leaveRequest.isChangeExpanded).toBe(false);
        });
      });

      describe('showModal', function () {
        beforeEach(function () {
          spyOn($uibModal, 'open');
          controller.showModal();
        });

        it('opens the modal', function () {
          expect($uibModal.open).toHaveBeenCalled();
        });
      });

      function compileComponent () {
        controller = $componentController('myLeaveContainer', null, { contactId: CRM.vars.leaveAndAbsences.contactId });
        $rootScope.$digest();
      }
    });
  });
})(CRM);
