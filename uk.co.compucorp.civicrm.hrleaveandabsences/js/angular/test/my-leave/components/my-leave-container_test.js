(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function (angular) {
    'use strict';

    describe('myLeaveContainer', function () {
      var $compile, $log, $rootScope, component, controller, $uibModal;

      beforeEach(module('leave-absences.templates', 'my-leave'));
      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$uibModal_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        $uibModal = _$uibModal_;
        spyOn($log, 'debug');

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('is contains the expected markup', function () {
        expect(component.find('div.my-leave-page').length).toBe(1);
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
        })
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

      function compileComponent() {
        var $scope = $rootScope.$new();
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<my-leave-container contact-id="' + contactId + '"></my-leave-container>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('myLeaveContainer');
      }
    });
  })
})(CRM);
