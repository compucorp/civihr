define([
    'common/angularMocks',
    'appraisals/app',
    'mocks/models/appraisal-cycle'
], function () {
    'use strict';

    describe('AppraisalsCtrl', function () {
        var $controller, $log, $modal, $rootScope, $scope, ctrl, dialog,
            AppraisalCycle, cycle;

        beforeEach(module('appraisals', 'appraisals.mocks'));
        beforeEach(inject(function (_$log_, _$modal_, _$rootScope_, _$controller_, _dialog_, _AppraisalCycleMock_) {
            ($modal = _$modal_) && spyOn($modal, 'open');
            ($log = _$log_) && spyOn($log, 'debug');

            $controller = _$controller_;
            $rootScope = _$rootScope_;
            $scope = $rootScope.$new();

            dialog = _dialog_;
            AppraisalCycle = _AppraisalCycleMock_;

            cycle = AppraisalCycle.mockedCycles().list[0];

            initController();
        }));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('has no cycle data loaded', function () {
                expect(ctrl.cycle).toBeDefined();
                expect(ctrl.cycle).toEqual(jasmine.any(Object));
            });

            it('contains grades', function () {
                expect(ctrl.grades).toBeDefined();
                expect(ctrl.grades).toEqual(jasmine.any(Array));
            });

            it('has the filters form collapsed', function () {
                expect(ctrl.filtersCollapsed).toBe(true);
            });

            it('is loading the cycle data', function () {
                expect(ctrl.loading.cycle).toBe(true);
                expect(AppraisalCycle.find).toHaveBeenCalledWith(cycle.id);
            });

            describe('when the data has been loaded', function () {
                beforeEach(function () {
                    $scope.$digest();
                });

                it('marks the data as loaded', function () {
                    expect(ctrl.loading.cycle).toBe(false);
                });

                it('stores the data internally', function () {
                    expect(ctrl.cycle.name).toBe(cycle.name);
                })
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

        /**
         * Initializes the controllers with its dependencies injected
         */
        function initController() {
            ctrl = $controller('AppraisalCycleCtrl', {
                $scope: $scope,
                $stateParams: {
                    cycleId: cycle.id
                },
                AppraisalCycle: AppraisalCycle
            });
        }
    });
});
