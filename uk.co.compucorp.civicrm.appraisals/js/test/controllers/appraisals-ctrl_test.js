define([
    'common/angularMocks',
    'common/mocks/services/hr-settings-mock',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsCtrl', function () {
        var $log, $provide, ctrl;

        beforeEach(function () {
            module('appraisals', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });
            inject(['HR_settingsMock', function (HR_settingsMock) {
                $provide.value('HR_settings', HR_settingsMock);
            }]);
        });

        beforeEach(inject(function ($rootScope, _$log_, $controller) {
            ($log = _$log_) && spyOn($log, 'debug');

            ctrl = $controller('AppraisalsCtrl', { $scope: $rootScope.$new() });
        }));

        it('is initialized', function () {
            expect($log.debug).toHaveBeenCalled();
        });

        describe('Add Appraisal Cycle modal', function () {
            var $modal;

            beforeEach(inject(function (_$uibModal_) {
                ($modal = _$uibModal_) && spyOn($modal, 'open');
            }));

            it('opens the modal', function () {
                ctrl.openAppraisalCycleModal();
                expect($modal.open).toHaveBeenCalled();
            });
        });
    });
})
