define([
    'common/angularMocks',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsCtrl', function () {
        var $log, ctrl;

        beforeEach(module('appraisals'));

        beforeEach(inject(function ($rootScope, _$log_, $controller) {
            ($log = _$log_) && spyOn($log, 'debug');

            ctrl = $controller('AppraisalsCtrl', { $scope: $rootScope.$new() });
        }));

        it('is initialized', function () {
            expect($log.debug).toHaveBeenCalled();
        });

        describe('Add Appraisal Cycle modal', function () {
            var $modal;

            beforeEach(inject(function (_$modal_) {
                ($modal = _$modal_) && spyOn($modal, 'open');
            }));

            it('opens the modal', function () {
                ctrl.openAddAppraisalCycleModal();
                expect($modal.open).toHaveBeenCalled();
            });
        });
    });
})
