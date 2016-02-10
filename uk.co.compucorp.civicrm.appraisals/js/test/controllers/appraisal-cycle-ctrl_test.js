define([
    'common/angularMocks',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsCtrl', function () {
        var $log, $modal, ctrl;

        beforeEach(module('appraisals'));
        beforeEach(inject(function (_$log_, _$modal_, $controller, $rootScope) {
            ($modal = _$modal_) && spyOn($modal, 'open');
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

            it('has the filters form collapsed', function () {
                expect(ctrl.filtersCollapsed).toBe(true);
            });
        });

        describe('Edit Dates modal', function () {
            beforeEach(function () {
                ctrl.openEditDatesModal();
            });

            it('opens the modal', function () {
                expect($modal.open).toHaveBeenCalled();
            });
        });

        describe('Access Settings modal', function () {
            beforeEach(function () {
                ctrl.openAccessSettingsModal();
            });

            it('opens the modal', function () {
                expect($modal.open).toHaveBeenCalled();
            });
        });

        describe('Add Contacts modal', function () {
            beforeEach(function () {
                ctrl.openAddContactsModal();
            });

            it('opens a modal', function () {
                expect($modal.open).toHaveBeenCalled();
            });
        });
    });
});
