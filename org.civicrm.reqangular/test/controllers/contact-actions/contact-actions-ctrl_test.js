/* eslint angular/di: 0, jasmine/no-spec-dupes: 0 */

define([
  'common/angularMocks',
  'common/controllers/contact-actions/contact-actions-ctrl'
], function (ctrl) {
  'use strict';

  describe('ContactActionsCtrl', function () {
    var ctrl, modalSpy;

    beforeEach(module('common.controllers'));
    beforeEach(inject(function (_$controller_, _$rootScope_) {
      modalSpy = jasmine.createSpyObj('modalSpy', ['open']);
      ctrl = _$controller_('ContactActionsCtrl', {
        $scope: _$rootScope_.$new(),
        $uibModal: modalSpy
      });
    }));

    describe('showNewIndividualModal', function () {
      beforeEach(function () {
        ctrl.showNewIndividualModal();
      });

      it('opens the modal', function () {
        expect(modalSpy.open).toHaveBeenCalled();
      });
    });

    describe('showNewHouseholdModal', function () {
      beforeEach(function () {
        ctrl.showNewHouseholdModal();
      });

      it('opens the modal', function () {
        expect(modalSpy.open).toHaveBeenCalled();
      });
    });

    describe('showNewOrganizationModal', function () {
      beforeEach(function () {
        ctrl.showNewOrganizationModal();
      });

      it('opens the modal', function () {
        expect(modalSpy.open).toHaveBeenCalled();
      });
    });
  });
});
