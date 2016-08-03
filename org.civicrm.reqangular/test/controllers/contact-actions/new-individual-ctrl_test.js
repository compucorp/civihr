/* eslint angular/di: 0 */

define([
  'common/angularMocks',
  'common/controllers/contact-actions/new-individual-ctrl'
], function () {
  'use strict';

  describe('NewIndividualModalCtrl', function () {
    var ctrl, $rootScope, $q, modalInstanceSpy, contactActionsSpy, resultMock;

    beforeEach(module('common.apis', 'common.controllers'));
    beforeEach(inject(function (_$controller_, _$rootScope_, _$q_) {
      $rootScope = _$rootScope_;
      $q = _$q_;
      initSpies();
      ctrl = _$controller_('NewIndividualModalCtrl', {
        '$rootScope': $rootScope,
        '$uibModalInstance': modalInstanceSpy,
        'api.contactActions': contactActionsSpy
      });
      $rootScope.$digest();
    }));

    /**
     * Jasmine spies initialization
     */
    function initSpies() {
      modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss']);
      contactActionsSpy = jasmine.createSpyObj('contactActionsSpy', ['saveNewIndividual']);
      resultMock = {
        test: true
      };
      contactActionsSpy.saveNewIndividual.and.returnValue($q.resolve(resultMock));
    }

    describe('cancel', function () {
      it('closes the modal instance', function () {
        ctrl.cancel();
        expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
      });
    });

    describe('submit', function () {
      beforeEach(function () {
        ctrl.firstName = 'First Name';
        ctrl.lastName = 'Last Name';
        ctrl.email = 'Email';
      });

      describe('when there are no errors', function () {
        beforeEach(function () {
          ctrl.submit();
          spyOn($rootScope, '$broadcast');
          $rootScope.$digest();
        });

        it('saves the new contact', function () {
          expect(contactActionsSpy.saveNewIndividual.calls.count()).toBe(1);
          expect(contactActionsSpy.saveNewIndividual).toHaveBeenCalledWith('First Name', 'Last Name', 'Email');
        });

        it('broadcasts the "newIndividualCreated" event', function () {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('newIndividualCreated', resultMock);
        });

        it('doesn\'t set the error message', function () {
          expect(ctrl.errorMsg.length).toBe(0);
        });
      });

      describe('when there are errors', function () {
        beforeEach(function () {
          contactActionsSpy.saveNewIndividual.and.returnValue($q.reject());
          ctrl.submit();
          spyOn($rootScope, '$broadcast');
          $rootScope.$digest();
        });

        it('doesn\'t broadcast events', function () {
          expect($rootScope.$broadcast).not.toHaveBeenCalled();
        });

        it('sets the error message', function () {
          expect(contactActionsSpy.saveNewIndividual.calls.count()).toBe(1);
          expect(ctrl.errorMsg.length).not.toBe(0);
        });
      });
    });
  });
});
