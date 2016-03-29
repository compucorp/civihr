define([
    'common/angularMocks',
    'common/mocks/services/hr-settings-mock',
    'common/mocks/services/api/appraisal-mock',
    'common/mocks/services/api/appraisal-cycle-mock',
    'appraisals/app'
], function () {
    'use strict';

    describe('AppraisalCycleCtrl', function () {
        var $controller, $log, $modal, $provide, $q, $rootScope, $scope, ctrl,
            dialog, Appraisal, AppraisalCycle, appraisalAPI, appraisalCycleAPI,
            cycle;

        beforeEach(function () {
            module('appraisals', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });
            // Override api.appraisal-cycle with the mocked version
            inject(['api.appraisal.mock', 'api.appraisal-cycle.mock', 'HR_settingsMock',
                function (_appraisalAPIMock_, _appraisalCycleAPIMock_, HR_settingsMock) {
                    appraisalAPI = _appraisalAPIMock_;
                    appraisalCycleAPI = _appraisalCycleAPIMock_;

                    $provide.value('api.appraisal', appraisalAPI);
                    $provide.value('api.appraisal-cycle', appraisalCycleAPI);
                    $provide.value('HR_settings', HR_settingsMock);
                }
            ]);
        });

        beforeEach(inject(function (_$log_, _$modal_, _$q_, _$rootScope_, _$controller_, _dialog_, _AppraisalCycle_) {
            ($modal = _$modal_) && spyOn($modal, 'open');
            ($log = _$log_) && spyOn($log, 'debug');

            $controller = _$controller_;
            $q = _$q_;
            $rootScope = _$rootScope_;
            $scope = $rootScope.$new();

            dialog = _dialog_;
            AppraisalCycle = _AppraisalCycle_;

            cycle = appraisalCycleAPI.mockedCycles().list[0];

            spyOn(AppraisalCycle, 'find').and.callThrough();
            initController();
        }));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });

            it('stores on scope the data passed by ui-router', function () {
                expect(ctrl.statuses).toBeDefined();
                expect(ctrl.types).toBeDefined();
            });

            it('has no cycle data loaded', function () {
                expect(ctrl.cycle).toBeDefined();
                expect(ctrl.cycle).toEqual(jasmine.any(Object));
            });

            it('is loading the data', function () {
                expect(ctrl.loading.cycle).toBe(true);
                expect(ctrl.loading.appraisals).toBe(true);
            });

            it('requests the cycle data', function () {
                expect(AppraisalCycle.find).toHaveBeenCalledWith(cycle.id);
            });

            describe('when the data has been loaded', function () {
                beforeEach(function () {
                    $scope.$digest();
                });

                it('marks the data as loaded', function () {
                    expect(ctrl.loading.cycle).toBe(false);
                    expect(ctrl.loading.appraisals).toBe(false);
                });

                it('stores the data internally', function () {
                    expect(ctrl.cycle.cycle_name).toBe(cycle.cycle_name);
                });
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

        describe('update()', function () {
            beforeEach(function () {
                $scope.$digest();
                spyOn(ctrl.cycle, 'update');

                ctrl.cycle.cycle_name = 'foo';
            });

            describe('dialog', function () {
                beforeEach(function () {
                    resolveDialogWith(null);
                    ctrl.update();

                    $scope.$digest();
                });

                it('shows a confirmation dialog', function () {
                    expect(dialog.open).toHaveBeenCalled();
                });
            });

            describe('when dialog is confirmed', function () {
                beforeEach(function () {
                    resolveDialogWith(true);

                    ctrl.update();
                    $scope.$digest();
                });

                it('updates the cycle', function () {
                    expect(ctrl.cycle.update).toHaveBeenCalled();
                    expect(ctrl.cycle.cycle_name).toBe('foo');
                });
            });

            describe('when dialog is rejected', function () {
                beforeEach(function () {
                    resolveDialogWith(false);

                    ctrl.update();
                    $scope.$digest();
                });

                it('does not do anything', function () {
                    expect(ctrl.cycle.update).not.toHaveBeenCalled();
                });

                it('reverts back to the original values', function () {
                    expect(ctrl.cycle.cycle_name).not.toBe('foo');
                });
            });
        });

        describe('when the data has been edited by another controller', function () {
            var newData;

            beforeEach(function () {
                $scope.$digest();

                newData = _.assign({}, ctrl.cycle, { cycle_name: 'foo bar' });

                $rootScope.$emit('AppraisalCycle::edit', newData);
                $scope.$digest();
            });

            it('updates the cycle in the controller', function () {
                expect(ctrl.cycle.cycle_name).toBe(newData.cycle_name);
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
                AppraisalCycle: AppraisalCycle,
                statuses: [],
                types: []
            });
        }

        /**
         * Spyes on dialog.open() method and resolves it with the given value
         *
         * @param {any} value
         */
        function resolveDialogWith(value) {
            var spy;

            if (typeof dialog.open.calls !== 'undefined') {
                spy = dialog.open;
            } else {
                spy = spyOn(dialog, 'open');
            }

            spy.and.callFake(function () {
                var deferred = $q.defer();
                deferred.resolve(value);

                return deferred.promise;
            });;
        }
    });
});
