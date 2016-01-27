define([
    'common/angular',
    'common/lodash',
    'common/angularMocks',
    'appraisals/app',
    'mocks/models/appraisal-cycle',
    'mocks/models/instances/appraisal-cycle-instance'
], function (angular, _) {
    'use strict';

    describe('AppraisalCycleModalCtrl', function () {
        var $compile, $controller, $q, $modalInstance, $rootScope, $scope, $templateCache,
            AppraisalCycle, AppraisalCycleInstance, ctrl, validCycle;

        validCycle = {
            cycle_name: 'Appraisal Cycle #1',
            cycle_type_id: '1',
            cycle_is_active: true,
            cycle_start_date: '01/01/2015',
            cycle_end_date: '31/12/2015',
            cycle_self_appraisal_due: '31/01/2016',
            cycle_manager_appraisal_due: '28/02/2016',
            cycle_grade_due: '30/03/2016'
        };

        beforeEach(module('appraisals', 'appraisals.mocks', 'appraisals.templates'));
        beforeEach(inject(function (_$compile_, _$controller_, _$q_, _$rootScope_, _$templateCache_, _AppraisalCycleMock_, _AppraisalCycleInstanceMock_) {
            $compile = _$compile_;
            $controller = _$controller_;
            $q = _$q_;
            $modalInstance = jasmine.createSpyObj('modalInstance', ['close']);
            $rootScope = _$rootScope_;
            $scope = $rootScope.$new();
            $templateCache = _$templateCache_;

            AppraisalCycle = _AppraisalCycleMock_;
            AppraisalCycleInstance = _AppraisalCycleInstanceMock_;

            initController();
        }));

        describe('inheritance', function () {
            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        describe('init', function () {
            it('marks the form as not submitted', function () {
                expect(ctrl.formSubmitted).toBe(false);
            });

            it('has an empty list of form errors', function () {
                expect(ctrl.formErrors).toEqual({});
            });

            describe('cycle types list', function () {
                it('waits for data to be loaded', function () {
                    expect(ctrl.loaded.types).toBe(false);
                });

                it('requests the list to the model', function () {
                    expect(AppraisalCycle.types).toHaveBeenCalled();
                });

                describe('when the model returns the data', function () {
                    beforeEach(function () {
                        $rootScope.$digest();
                    });

                    it('marks the list as loaded', function () {
                        expect(ctrl.loaded.types).toBe(true);
                    });
                });
            });

            describe('when in "create mode"', function () {
                it('marks the flag as such', function () {
                    expect(ctrl.edit).toBe(false);
                });

                it('does not fetch the data of any cycle', function () {
                    expect(AppraisalCycle.find).not.toHaveBeenCalled();
                    expect(ctrl.cycle).toEqual({});
                    expect(ctrl.loaded.cycle).toBe(true);
                });
            });

            describe('when in "edit mode', function () {
                var $scope;

                beforeEach(function () {
                    $scope = $rootScope.$new();
                    $scope.cycleId = '6';

                    initController({ $scope: $scope });
                });

                it('marks the flag as such', function () {
                    expect(ctrl.edit).toBe(true);
                });

                it('waits for the data to be loaded', function () {
                    expect(ctrl.loaded.cycle).toBe(false);
                });

                it('fetches the data of the cycle with the given id', function () {
                    expect(AppraisalCycle.find).toHaveBeenCalledWith($scope.cycleId);
                });

                describe('when the model returns the data', function () {
                    beforeEach(function () {
                        $rootScope.$digest();
                    });

                    it('marks the list as loaded', function () {
                        expect(ctrl.loaded.cycle).toBe(true);
                    });
                });
            })
        });

        describe('form validation', function () {
            beforeEach(function () {
                initForm();
            });

            describe('valid data', function () {
                beforeEach(function () {
                    prepFormWith(validCycle);
                });

                it('submits the form when validation is passed', function () {
                    expect(ctrl.form.$valid).toBe(true);
                    expect(AppraisalCycle.create).toHaveBeenCalled();
                });
            });

            describe('mandatory fields', function () {
                beforeEach(function () {
                    prepFormWith(_.omit(validCycle, ['cycle_name', 'cycle_grade_due']));
                });

                it('must be present', function () {
                    expect(ctrl.form.$valid).toBe(false);
                    expect(ctrl.form.cycle_name.$error.required).toBe(true);
                    expect(ctrl.form.cycle_grade_due.$error.required).toBe(true);
                    expect(ctrl.form.cycle_type_id.$error.required).toBe(false);
                    expect(AppraisalCycle.create).not.toHaveBeenCalled();
                });
            });

            describe('end date', function () {
                beforeEach(function () {
                    prepFormWith(_.assign({}, validCycle, { cycle_end_date: '31/12/2014' }));
                });

                it('end date must be after end date', function () {
                    expect(ctrl.form.$valid).toBe(false);
                    expect(ctrl.form.cycle_end_date.$error.isAfter).toBe(true);
                    expect(AppraisalCycle.create).not.toHaveBeenCalled();
                });
            });

            describe('manager appraisal due date', function () {
                beforeEach(function () {
                    prepFormWith(_.assign({}, validCycle, { cycle_manager_appraisal_due: '05/01/2016' }));
                });

                it('manager appraisal due date must be after self appraisal due date', function () {
                    expect(ctrl.form.$valid).toBe(false);
                    expect(ctrl.form.cycle_manager_appraisal_due.$error.isAfter).toBe(true);
                    expect(AppraisalCycle.create).not.toHaveBeenCalled();
                });
            });

            describe('grade due date', function () {
                beforeEach(function () {
                    prepFormWith(_.assign({}, validCycle, { cycle_grade_due: '10/02/2016' }));
                });

                it('grade due date must be after manager appraisal due date', function () {
                    expect(ctrl.form.$valid).toBe(false);
                    expect(ctrl.form.cycle_grade_due.$error.isAfter).toBe(true);
                    expect(AppraisalCycle.create).not.toHaveBeenCalled();
                });
            });
        });

        describe('form errors', function () {
            var cycleWithErrors = _.assign({}, validCycle, {
                cycle_name: '',
                cycle_end_date: '31/12/2014',
                cycle_grade_due: '',
                cycle_manager_appraisal_due: '20/01/2016'
            });

            beforeEach(function () {
                initForm();
                prepFormWith(cycleWithErrors);
            });

            it('returns the list of fields, each with its own errors', function () {
                expect(ctrl.formErrors).toEqual({
                    cycle_name: { required: true },
                    cycle_end_date: { isAfter: true },
                    cycle_grade_due: { required: true, isAfter: true },
                    cycle_manager_appraisal_due: { isAfter: true }
                })
            });
        });

        describe('form submit', function () {
            beforeEach(function () {
                initForm();
                spyOn($rootScope, '$emit');
            });

            describe('submit status', function () {
                beforeEach(function () {
                    prepFormWith(validCycle);
                });

                it('marks the form as submitted', function () {
                    expect(ctrl.formSubmitted).toBe(true);
                });
            });

            describe('when in "create mode', function () {
                beforeEach(function () {
                    prepFormWith(validCycle);
                });

                it('sends a request to the api with the new cycle data', function () {
                    expect(AppraisalCycle.create).toHaveBeenCalledWith(validCycle);
                });

                it('emits an event', function () {
                    expect($rootScope.$emit).toHaveBeenCalledWith('AppraisalCycle::new', jasmine.any(Object));
                });

                it('closes the modal', function () {
                    expect($modalInstance.close).toHaveBeenCalled();
                });
            });

            describe('when in "edit mode"', function () {
                var editedCycle = _.assign({}, validCycle, { id: '657', cycle_name: 'Amended name', cycle_type_id: '2' });

                beforeEach(function () {
                    ctrl.edit = true;
                    prepFormWith(_.omit(editedCycle, 'id'), AppraisalCycleInstance.init(editedCycle));
                });

                it('triggers the update on the model instance', function () {
                    expect(ctrl.cycle.update).toHaveBeenCalled();
                });

                describe('event', function () {
                    it('is emitted', function () {
                        expect($rootScope.$emit).toHaveBeenCalledWith('AppraisalCycle::edit', jasmine.any(Object));
                    });

                    it('gets the same cycle object passed as parameter', function () {
                        expect($rootScope.$emit.calls.argsFor(0)[1]).toBe(ctrl.cycle);
                    });
                });

                it('closes the modal', function () {
                    expect($modalInstance.close).toHaveBeenCalled();
                });
            });
        });

        /**
         * Prepares the form with the given values and then runs the digest
         * cycles
         *
         * @param {object} formValues
         * @param {object} cycleInScope - If specified, this object will be used
         *   as the cycle in the ctrl's scope instead of the form values
         */
        function prepFormWith(formValues, cycleInScope) {
            _.forEach(formValues, function (value, field) {
                ctrl.form[field].$setViewValue(value);
            }) && $rootScope.$digest();

            ctrl.cycle = cycleInScope || formValues;
            ctrl.submit();

            $rootScope.$digest();
        }

        /**
         * Initializes the controller with additional injected values
         *
         * @param {object} params - The values to inject in the controller
         */
        function initController(params) {
            ctrl = $controller('AppraisalCycleModalCtrl', angular.extend({}, {
                $modalInstance: $modalInstance,
                $scope: $scope,
                AppraisalCycle: AppraisalCycle
            }, params));
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
            var template = $templateCache.get(CRM.vars.appraisals.baseURL + '/views/modals/appraisal-cycle.html');
            template = template.replace(/datepicker-popup=(.+) ?/g, '');

            $compile(angular.element(template))($rootScope);

            ctrl.form = $rootScope.modal.form;
        }
    });
});
