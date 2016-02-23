define([
    'common/lodash',
    'common/angularMocks',
    'common/mocks/services/api/appraisal-cycle-mock',
    'appraisals/app'
], function (_) {
    'use strict';

    describe('EditDatesModalCtrl', function () {
        var $compile, $controller, $filter, $modalInstance, $provide, $rootScope,
        $templateCache, $scope, appraisalCycleAPIMock, cycle, ctrl;

        beforeEach(function () {
            module('common.mocks', 'appraisals', 'appraisals.templates', function (_$provide_) {
                $provide = _$provide_;
            });
            // Override api.appraisal-cycle with the mocked version
            inject(['api.appraisal-cycle.mock',
                function (_appraisalCycleAPIMock_) {
                    appraisalCycleAPIMock = _appraisalCycleAPIMock_;

                    $provide.value('api.appraisal-cycle', appraisalCycleAPIMock);
                }
            ]);
        });
        beforeEach(inject([
            '$compile', '$controller', '$filter', '$rootScope', '$templateCache',
            'AppraisalCycleInstance',
            function (_$compile_, _$controller_, _$filter_, _$rootScope_, _$templateCache_, AppraisalCycleInstance) {
                cycle = AppraisalCycleInstance.init(
                    appraisalCycleAPIMock.mockedCycles().list[2]
                );

                $compile = _$compile_;
                $controller = _$controller_;
                $templateCache = _$templateCache_;
                $rootScope = _$rootScope_;

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

                ctrl.submit();
                $rootScope.$digest();
            });

            it('marks the form as submitted', function () {
                expect(ctrl.formSubmitted).toBe(true);
            });

            it('formats the datepicker dates', function () {
                expect($filter).toHaveBeenCalledWith('date');
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
