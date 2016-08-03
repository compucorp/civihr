/* eslint angular/di: 0 */

define([
  'common/angularMocks',
  'common/controllers/contact-actions/new-household-ctrl'
], function () {
  'use strict';

  describe('NewHouseholdModalCtrl', function () {
    var ctrl, $rootScope, $q, modalInstanceSpy, contactActionsSpy, resultMock;

    beforeEach(module('common.apis', 'common.controllers'));
    beforeEach(inject(function (_$controller_, _$rootScope_, _$q_) {
      $rootScope = _$rootScope_;
      $q = _$q_;
      initSpies();
      ctrl = _$controller_('NewHouseholdModalCtrl', {
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
      contactActionsSpy = jasmine.createSpyObj('contactActionsSpy', ['saveNewHousehold']);
      resultMock = {
        test: true
      };
      contactActionsSpy.saveNewHousehold.and.returnValue($q.resolve(resultMock));
    }

    describe('cancel', function () {
      it('closes the modal instance', function () {
        ctrl.cancel();
        expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
      });
    });

    describe('submit', function () {
      beforeEach(function () {
        ctrl.householdName = 'Household Name';
        ctrl.email = 'Email';
      });

      describe('when there are no errors', function () {
        beforeEach(function () {
          ctrl.submit();
          spyOn($rootScope, '$broadcast');
          $rootScope.$digest();
        });

        it('saves the new household', function () {
          expect(contactActionsSpy.saveNewHousehold.calls.count()).toBe(1);
          expect(contactActionsSpy.saveNewHousehold).toHaveBeenCalledWith('Household Name', 'Email');
        });

        it('broadcasts the "newHouseholdCreated" event', function () {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('newHouseholdCreated', resultMock);
        });

        it('doesn\'t set the error message', function () {
          expect(ctrl.errorMsg.length).toBe(0);
        });
      });

      describe('when there are errors', function () {
        beforeEach(function () {
          contactActionsSpy.saveNewHousehold.and.returnValue($q.reject());
          ctrl.submit();
          spyOn($rootScope, '$broadcast');
          $rootScope.$digest();
        });

        it('doesn\'t broadcast events', function () {
          expect($rootScope.$broadcast).not.toHaveBeenCalled();
        });

        it('sets the error message', function () {
          expect(contactActionsSpy.saveNewHousehold.calls.count()).toBe(1);
          expect(ctrl.errorMsg.length).not.toBe(0);
        });
      });
    });
  });
});
