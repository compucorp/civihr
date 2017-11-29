define([
  'common/angularMocks',
  'access-rights/controllers/access-rights.controller'
], function () {
  'use strict';

  describe('AccessRightsCtrl', function () {
    var ctrl, modalSpy;

    beforeEach(module('access-rights.controllers'));
    beforeEach(inject(function (_$controller_, _$rootScope_) {
      modalSpy = jasmine.createSpyObj('modalSpy', ['open']);
      ctrl = _$controller_('AccessRightsCtrl', {
        $scope: _$rootScope_.$new(),
        $uibModal: modalSpy
      });
    }));

    describe('openModal', function () {
      beforeEach(function () {
        ctrl.openModal();
      });

      it('opens the modal', function () {
        expect(modalSpy.open).toHaveBeenCalled();
      });
    });
  });
});
