define([
    'common/lodash',
    'common/angularMocks',
    'common/mocks/services/hr-settings-mock',
    'common/mocks/services/api/appraisal-cycle-mock',
    'appraisals/app'
], function (_) {
    'use strict';

    describe('EditDatesModalCtrl', function () {
        var $compile, $controller, $filter, $q, $modalInstance, $provide, $rootScope,
        $templateCache, $scope, appraisalCycleAPIMock, cycle, ctrl, dialog;

        beforeEach(function () {
            module('common.mocks', 'appraisals', 'appraisals.templates', function (_$provide_) {
                $provide = _$provide_;
            });
            // Override api.appraisal-cycle with the mocked version
            inject(['api.appraisal-cycle.mock', 'HR_settingsMock',
                function (_appraisalCycleAPIMock_, HR_settingsMock) {
                    appraisalCycleAPIMock = _appraisalCycleAPIMock_;

                    $provide.value('api.appraisal-cycle', appraisalCycleAPIMock);
                    $provide.value('HR_settings', HR_settingsMock);
                }
            ]);
        });
        beforeEach(inject([
            '$compile', '$controller', '$filter', '$q', '$rootScope', '$templateCache',
            'AppraisalCycleInstance', 'dialog',
            function (_$compile_, _$controller_, _$filter_, _$q_, _$rootScope_, _$templateCache_, AppraisalCycleInstance, _dialog_) {
                cycle = AppraisalCycleInstance.init(
                    appraisalCycleAPIMock.mockedCycles().list[2],
                    true
                );

                $compile = _$compile_;
                $controller = _$controller_;
                $q = _$q_;
                $templateCache = _$templateCache_;
                $rootScope = _$rootScope_;
                dialog = _dialog_

                initSpies(_$filter_);
                initController();
                initForm();

                spyOn(ctrl.cycle, 'update').and.callThrough();
            }
        ]));

        describe('inheritance', function () {
            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        describe('init()', function () {
            it('marks the form as not submitted', function () {
                expect(ctrl.formSubmitted).toBe(false);
            });

            it('has an empty list of form errors', function () {
                expect(ctrl.formErrors).toEqual({});
            });

            it('contains the cycle in its scope', function () {
                expect(ctrl.cycle).toBeDefined();
                expect(ctrl.cycle.id).toBe(cycle.id);
            });

            it('has made a copy of the cycle, not working directly on it', function () {
                expect(ctrl.cycle).not.toBe(cycle);
            });
        });

        describe('form validation', function () {
            var validData = {
                cycle_self_appraisal_due: '01/01/2001',
                cycle_manager_appraisal_due: '02/02/2002',
                cycle_grade_due: '03/03/2003',
            };

            describe('valid data', function () {
                beforeEach(function () {
                    submitFormWith(validData);
                });

                it('submits the form when validation is passed', function () {
                    expect(ctrl.form.$valid).toBe(true);
                });
            });

            describe('mandatory fields', function () {
                beforeEach(function () {
                    submitFormWith(_.omit(validData, ['cycle_self_appraisal_due', 'cycle_grade_due']));
                });

                it('must be present', function () {
                    expect(ctrl.form.$valid).toBe(false);
                    expect(ctrl.form.cycle_self_appraisal_due.$error.required).toBe(true);
                    expect(ctrl.form.cycle_grade_due.$error.required).toBe(true);
                    expect(ctrl.cycle.update).not.toHaveBeenCalled();
                });
            });
        });

        describe('form errors', function () {
            var invalidData = {
                cycle_self_appraisal_due: '01/01/2001'
            };

            beforeEach(function () {
                submitFormWith(invalidData);
            });

            it('returns the list of fields, each with its own errors', function () {
                expect(ctrl.formErrors).toEqual({
                    cycle_grade_due: { required: true },
                    cycle_manager_appraisal_due: { required: true }
                });
            });
        });

        describe('submit()', function () {
            beforeEach(function () {
                spyOn($rootScope, '$emit');
            });

            describe('standard submit (dates have been changed)', function () {
                var dates = {
                    cycle_self_appraisal_due: '01/01/2001',
                    cycle_manager_appraisal_due: '02/02/2002',
                    cycle_grade_due: '03/03/2003'
                };

                describe('before the dialog shows up', function () {
                    beforeEach(function () {
                        resolveDialogWith(null);
                        submitFormWith(dates);
                    });

                    it('marks the form as submitted', function () {
                        expect(ctrl.formSubmitted).toBe(true);
                    });

                    it('formats the datepicker dates', function () {
                        expect($filter).toHaveBeenCalledWith('date');
                    });

                    it('shows a confirmation dialog', function () {
                        expect(dialog.open).toHaveBeenCalled();
                    });
                });

                describe('when the dialog is confirmed', function () {
                    beforeEach(function () {
                        resolveDialogWith(true);
                        submitFormWith(dates);
                    });

                    it('updates the cycle', function () {
                        expect(ctrl.cycle.update).toHaveBeenCalled();
                    });

                    it('emits an event', function () {
                        expect($rootScope.$emit).toHaveBeenCalledWith('AppraisalCycle::edit', ctrl.cycle);
                    });

                    it('closes the modal', function () {
                        expect($modalInstance.close).toHaveBeenCalled();
                    });
                });

                describe('when the dialog is rejected', function () {
                    beforeEach(function () {
                        resolveDialogWith(false);
                        submitFormWith(dates);
                    });

                    it('does not do anything', function () {
                        expect(ctrl.cycle.update).not.toHaveBeenCalled();
                    });
                });
            });

            describe('submit with unchanged dates', function () {
                beforeEach(function () {
                    resolveDialogWith(null);
                    submitFormWith(ctrl.cycle.dueDates());
                });

                it('does not show a confirmation dialog', function () {
                    expect(dialog.open).not.toHaveBeenCalled();
                });

                it('goes straight to the cycle update', function () {
                    expect(ctrl.cycle.update).toHaveBeenCalled();
                });
            });
        });

        /**
         * Creates fake functions to inject in the controller
         */
        function initSpies(_$filter_) {
            $modalInstance = jasmine.createSpyObj('modalInstance', ['close']);
            $filter = jasmine.createSpy('filter').and.callFake(function (filter) {
                return _$filter_.apply(null, arguments);
            });
        }

        /**
         * Initializes the controller with fake dependencies
         */
        function initController() {
            ctrl = $controller('EditDatesModalCtrl', {
                $filter: $filter,
                $modalInstance: $modalInstance,
                $scope: (function (scope) {
                    scope.cycle = cycle;
                    return scope;
                })($rootScope.$new())
            });
        }

        /**
         * Initializes the form the modal controller is tied to.
         *
         * It is necessary to remove the reference to the datepicker directive
         * otherwise it will interfere with the direct insertions of values
         * in the fields the directive it is applied to
         *
         * It compiles it against a scope and then assigns it to the
         * internal `form` property (because of the "controller as" syntax)
         */
        function initForm() {
            var template = $templateCache.get(CRM.vars.appraisals.baseURL + '/views/modals/edit-dates.html');
            var $scope = $rootScope.$new();

            template = template.replace(/datepicker-popup=(.+) ?/g, '');
            $compile(angular.element(template))($scope);

            ctrl.form = $scope.modal.form;
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

        /**
         * Prepares the form with the given values and then runs the digest
         * cycles
         *
         * @param {object} formValues
         */
        function submitFormWith(formValues) {
            _.forEach(formValues, function (value, field) {
                ctrl.form[field].$setViewValue(value);
            }) && $rootScope.$digest();

            ctrl.cycle = _.assign(ctrl.cycle, formValues);

            ctrl.submit();
            $rootScope.$digest();
        }
    });
});
