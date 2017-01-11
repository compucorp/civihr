(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/shared/directives/leave-request-popup',
  ], function (angular) {
    'use strict';

    describe('leaveRequestPopup', function () {
      var $compile, $log, $rootScope, directive, $uibModal,
        $controllerScope, $provide, DateFormat;

      beforeEach(module('leave-absences.templates', 'leave-absences.directives'));

      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$uibModal_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        $uibModal = _$uibModal_;
        spyOn($log, 'debug');

        compileDirective();
      }));

      it('is called', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('dialog is open', function () {
        beforeEach(function () {
          spyOn($uibModal, 'open');
          directive.triggerHandler('click');
          $controllerScope.$digest();
        });

        it('opens dependent popup', function () {
          expect($uibModal.open).toHaveBeenCalledWith(jasmine.any(Object));
        });
      });

      /**
       * Creates and compiles the directive
       */
      function compileDirective() {
        $controllerScope = $rootScope.$new();
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        directive = angular.element('<leave-request-popup contact-id="' + contactId + '"></leave-request-popup>');
        $compile(directive)($controllerScope);
        $controllerScope.$digest();
      }
    });
  });
})(CRM);
