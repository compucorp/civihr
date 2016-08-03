/* eslint angular/di: 0 */

define([
  'common/angularMocks',
  'common/controllers/contact-actions/new-organization-ctrl'
], function () {
  'use strict';

  describe('NewOrganizationModalCtrl', function () {
    var ctrl, $rootScope, $q, modalInstanceSpy, contactActionsSpy, resultMock;

    beforeEach(module('common.apis', 'common.controllers'));
    beforeEach(inject(function (_$controller_, _$rootScope_, _$q_) {
      $rootScope = _$rootScope_;
      $q = _$q_;
      initSpies();
      ctrl = _$controller_('NewOrganizationModalCtrl', {
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
      contactActionsSpy = jasmine.createSpyObj('contactActionsSpy', ['saveNewOrganization']);
      resultMock = {
        test: true
      };
      contactActionsSpy.saveNewOrganization.and.returnValue($q.resolve(resultMock));
    }

    describe('cancel', function () {
      it('closes the modal instance', function () {
        ctrl.cancel();
        expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
      });
    });

    describe('submit', function () {
      beforeEach(function () {
        ctrl.organizationName = 'Organization Name';
        ctrl.email = 'Email';
      });

      describe('when there are no errors', function () {
        beforeEach(function () {
          ctrl.submit();
          spyOn($rootScope, '$broadcast');
          $rootScope.$digest();
        });

        it('saves the new organization', function () {
          expect(contactActionsSpy.saveNewOrganization.calls.count()).toBe(1);
          expect(contactActionsSpy.saveNewOrganization).toHaveBeenCalledWith('Organization Name', 'Email');
        });

        it('broadcasts the "newOrganizationCreated" event', function () {
          expect($rootScope.$broadcast).toHaveBeenCalledWith('newOrganizationCreated', resultMock);
        });

        it('doesn\'t set the error message', function () {
          expect(ctrl.errorMsg.length).toBe(0);
        });
      });

      describe('when there are errors', function () {
        beforeEach(function () {
          contactActionsSpy.saveNewOrganization.and.returnValue($q.reject());
          ctrl.submit();
          spyOn($rootScope, '$broadcast');
          $rootScope.$digest();
        });

        it('doesn\'t broadcast events', function () {
          expect($rootScope.$broadcast).not.toHaveBeenCalled();
        });

        it('sets the error message', function () {
          expect(contactActionsSpy.saveNewOrganization.calls.count()).toBe(1);
          expect(ctrl.errorMsg.length).not.toBe(0);
        });
      });
    });
  });
});
