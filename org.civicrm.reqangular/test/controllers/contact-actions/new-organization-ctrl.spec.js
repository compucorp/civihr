/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/controllers/contact-actions/new-organization-ctrl'
], function () {
  'use strict';

  describe('NewOrganizationModalCtrl', function () {
    var ctrl, $rootScope, $q, modalInstanceSpy, contactActionsStub, resultMock;

    beforeEach(module('common.apis', 'common.controllers'));
    beforeEach(inject(function (_$controller_, _$rootScope_, _$q_) {
      $rootScope = _$rootScope_;
      $q = _$q_;
      initSpies();
      ctrl = _$controller_('NewOrganizationModalCtrl', {
        '$rootScope': $rootScope,
        '$uibModalInstance': modalInstanceSpy,
        'api.contactActions': contactActionsStub
      });
      $rootScope.$digest();
    }));

    /**
     * Jasmine spies initialization
     */
    function initSpies () {
      modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss']);
      contactActionsStub = {
        save: jasmine.createSpyObj('saveSpy', ['newOrganization']),
        getFormFields: jasmine.createSpyObj('getFormFieldsSpy', ['forNewOrganization'])
      };
      resultMock = {
        test: true
      };

      contactActionsStub.getFormFields.forNewOrganization.and.returnValue($q.resolve([{
        id: '1',
        label: 'Field1',
        field_name: 'field1'
      }, {
        id: '2',
        label: 'Field2',
        field_name: 'field2'
      }]));
      contactActionsStub.save.newOrganization.and.returnValue($q.resolve(resultMock));
    }

    describe('cancel', function () {
      it('closes the modal instance', function () {
        ctrl.cancel();
        expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
      });
    });

    describe('submit', function () {
      beforeEach(function () {
        ctrl.formFields[0].value = 'value1';
        ctrl.formFields[1].value = 'value2';
      });

      describe('when there are no errors', function () {
        beforeEach(function () {
          spyOn($rootScope, '$broadcast');
          ctrl.submit();
        });

        it('saves the new organization', function () {
          $rootScope.$digest();
          expect(contactActionsStub.save.newOrganization.calls.count()).toBe(1);
          expect(contactActionsStub.save.newOrganization).toHaveBeenCalledWith({
            field1: 'value1',
            field2: 'value2'
          });
        });

        it('broadcasts the "newOrganizationCreated" event', function () {
          $rootScope.$digest();
          expect($rootScope.$broadcast).toHaveBeenCalledWith('newOrganizationCreated', resultMock);
        });

        it('doesn\'t set the error message', function () {
          $rootScope.$digest();
          expect(ctrl.errorMsg.length).toBe(0);
        });

        it('changes the "loading" property', function () {
          expect(ctrl.loading).toBeTruthy();
          $rootScope.$digest();
          expect(ctrl.loading).toBeFalsy();
        });
      });

      describe('when there are errors', function () {
        beforeEach(function () {
          contactActionsStub.save.newOrganization.and.returnValue($q.reject());
          spyOn($rootScope, '$broadcast');
          ctrl.submit();
        });

        it('doesn\'t broadcast events', function () {
          $rootScope.$digest();
          expect($rootScope.$broadcast).not.toHaveBeenCalled();
        });

        it('sets the error message', function () {
          $rootScope.$digest();
          expect(contactActionsStub.save.newOrganization.calls.count()).toBe(1);
          expect(ctrl.errorMsg.length).not.toBe(0);
        });

        it('changes the "loading" property', function () {
          expect(ctrl.loading).toBeTruthy();
          $rootScope.$digest();
          expect(ctrl.loading).toBeFalsy();
        });
      });
    });
  });
});
