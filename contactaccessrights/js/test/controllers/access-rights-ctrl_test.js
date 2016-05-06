define([
  'common/angularMocks',
  'access-rights/controllers/access-rights-ctrl'
], function () {
  'use strict';

  describe('AccessRightsCtrl', function () {
    var ctrl, modalSpy;

    beforeEach(module('access-rights.controllers'));
    beforeEach(inject(function (_$controller_, _$rootScope_) {
      CRM = {
        vars: {
          contactAccessRights: {
            baseURL: ''
          }
        }
      };
      modalSpy = jasmine.createSpyObj('modalSpy', ['open']);
      ctrl = _$controller_('AccessRightsCtrl', {
        $scope: _$rootScope_.$new(),
        $modal: modalSpy
      });
    }));

    describe('openModal', function () {
      it('opens the modal', function () {
        ctrl.openModal();
        expect(modalSpy.open).toHaveBeenCalled();
      });
    });
  });
});
