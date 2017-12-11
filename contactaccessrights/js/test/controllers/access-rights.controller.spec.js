/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'access-rights/modules/access-rights.module'
], function () {
  'use strict';

  describe('AccessRightsController', function () {
    var ctrl, modalSpy;

    beforeEach(module('access-rights'));
    beforeEach(inject(function (_$controller_, _$rootScope_) {
      modalSpy = jasmine.createSpyObj('modalSpy', ['open']);
      ctrl = _$controller_('AccessRightsController', {
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
