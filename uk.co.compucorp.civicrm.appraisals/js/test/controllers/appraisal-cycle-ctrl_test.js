define([
    'common/angularMocks',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsCtrl', function () {
        var $log, ctrl;

        beforeEach(module('appraisals'));
        beforeEach(inject(function (_$log_, $controller, $rootScope) {
            ($log = _$log_) && spyOn($log, 'debug');

            ctrl = $controller('AppraisalCycleCtrl', { $scope: $rootScope.$new() });
        }));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('contains grades', function () {
                expect(ctrl.grades).toBeDefined();
            });
        });

        describe('Edit Dates modal', function () {
            var $modal;

            beforeEach(inject(function (_$modal_) {
                ($modal = _$modal_) && spyOn($modal, 'open');
            }));

            it('opens the modal', function () {
                ctrl.openEditDatesModal();
                expect($modal.open).toHaveBeenCalled();
            });
        });
    });
});
