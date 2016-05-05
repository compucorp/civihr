define([
	'common/angularMocks',
	'access-rights/controllers/access-rights-ctrl'
], function () {
	'use strict';

	describe('AccessRightsCtrl', function () {
		var ctrl, fakeModal;

		beforeEach(module('access-rights.controllers'));
		beforeEach(inject(function (_$controller_, _$rootScope_) {
			CRM = {
				vars: {
					contactAccessRights: {
						baseURL: ''
					}
				}
			};
			fakeModal = jasmine.createSpyObj('fakeModal', ['open']);
			ctrl = _$controller_('AccessRightsCtrl', {
				$scope: _$rootScope_.$new(),
				$modal: fakeModal
			});
		}));

		describe('openModal', function () {
			it('opens the modal', function () {
				ctrl.openModal();
				expect(fakeModal.open).toHaveBeenCalled();
			});
		});
	});
});
