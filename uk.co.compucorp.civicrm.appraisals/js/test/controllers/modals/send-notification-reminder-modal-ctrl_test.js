define([
    'common/angularMocks',
    'appraisals/app'
], function () {
    'use strict';

    describe('SendNotificationReminderModalCtrl', function () {
        var $modal, $modalInstance, ctrl;

        beforeEach(module('appraisals'));
        beforeEach(inject(function (_$modal_, $controller, $rootScope) {
            ($modal = _$modal_) && spyOn($modal, 'open');
            $modalInstance = jasmine.createSpyObj('modalInstance', ['close']);

            ctrl = $controller('SendNotificationReminderModalCtrl', {
                $modalInstance: $modalInstance,
                $scope: $rootScope.$new()
            });
        }));

        describe('inheritance', function () {
            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        describe('openNotificationRecipientsModal()', function () {
            beforeEach(function () {
                ctrl.openNotificationRecipientsModal();
            });

            it('opens the modal', function () {
                expect($modal.open).toHaveBeenCalled();
            });
        });
    });
})
