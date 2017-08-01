/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/shared/directives/leave-request-popup'
  ], function (angular) {
    'use strict';

    describe('leaveRequestPopup', function () {
      var $compile, $log, $rootScope, directive, $uibModal, $controllerScope;

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

        describe('with all attributes', function () {
          var isolateScope, contactId;

          beforeEach(function () {
            contactId = CRM.vars.leaveAndAbsences.contactId;
            isolateScope = directive.isolateScope();
          });

          it('sets contact id', function () {
            expect(isolateScope.contactId).toEqual(+contactId);
          });

          it('sets leave request', function () {
            expect(isolateScope.leaveRequest).toEqual({'contact-id': contactId});
          });
        });
      });

      /**
       * Creates and compiles the directive
       */
      function compileDirective () {
        $controllerScope = $rootScope.$new();
        var contactId = CRM.vars.leaveAndAbsences.contactId;
        var leaveRequest = {'contact-id': contactId};
        var elementString = '<leave-request-popup contact-id=' +
          contactId + " leave-type='sick' leave-request=" + JSON.stringify(leaveRequest) +
          '></leave-request-popup>';

        directive = angular.element(elementString);
        $compile(directive)($controllerScope);
        $controllerScope.$digest();
      }
    });
  });
})(CRM);
