define([
    'common/angularMocks',
    'appraisals/app',
], function () {
    'use strict';

    describe('AppraisalsCtrl', function () {
        var $log, $modal, ctrl, dialog;

        beforeEach(module('appraisals'));
        beforeEach(inject(function (_$log_, _$modal_, $controller, $rootScope, _dialog_) {
            ($modal = _$modal_) && spyOn($modal, 'open');
            ($log = _$log_) && spyOn($log, 'debug');

            dialog = _dialog_;
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

        describe('delete()', function () {
            beforeEach(function () {
                spyOn(dialog, 'open');
                ctrl.delete();
            });

            it('opens a dialog', function () {
                expect(dialog.open).toHaveBeenCalled();
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

        describe('View Cycle modal', function () {
            beforeEach(function () {
                ctrl.openViewCycleModal();
            });

            it('opens a modal', function () {
                expect($modal.open).toHaveBeenCalled();
            });
        });

        describe('Send Notification/Reminder Modal', function () {
            beforeEach(function () {
                ctrl.openSendNotificationReminderModal();
            });

            it('opens a modal', function () {
                expect($modal.open).toHaveBeenCalled();
            });
        });
    });
});
