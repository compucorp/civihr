define([
    'common/lodash',
    'common/moment',
    'appraisals/modules/controllers'
], function (_, moment, controllers) {
    'use strict';

    controllers.controller('AppraisalCycleModalCtrl',
        ['$filter', '$log', '$rootScope', '$scope', '$controller', '$modalInstance', 'AppraisalCycle',
        function ($filter, $log, $rootScope, $scope, $controller, $modalInstance, AppraisalCycle) {
            $log.debug('AppraisalCycleModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            vm.cycle = {};
            vm.types = [];

            vm.edit = false;
            vm.formSubmitted = false;
            vm.loaded = {
                types: false,
                cycle: !$scope.cycleId
            };

            /**
             * Adds the new cycle and on complete emits an event, closes the modal
             */
            vm.submit = function () {
                vm.formSubmitted = true;

                formatDates();

                if (isFormValid()) {
                    vm.edit ? editCycle() : createCycle();
                }
            };

            init();

            /**
             * Creates a new cycle
             */
            function createCycle() {
                AppraisalCycle.create(vm.cycle).then(function (cycle) {
                    $rootScope.$emit('AppraisalCycle::new', cycle);
                    $modalInstance.close();
                });
            }

            /**
             * Edits the current cycle
             */
            function editCycle() {
                vm.cycle.update().then(function () {
                    $rootScope.$emit('AppraisalCycle::edit', vm.cycle);
                    $modalInstance.close();
                });
            }

            /**
             * Formats all the dates in the current date format
             *
             * (Necessary because the date picker directives always return
             * a Date object instead of simply a string in the specified format)
             */
            function formatDates() {
                for (var key in vm.cycle) {
                    if (_.endsWith(key, '_date') || _.endsWith(key, '_due')) {
                        vm.cycle[key] = $filter('date')(vm.cycle[key], 'dd/MM/yyyy');
                    }
                }
            }

            /**
             * Initialization code
             */
            function init() {
                AppraisalCycle.types().then(function (types) {
                    vm.types = types;
                    vm.loaded.types = true;
                });

                if ($scope.cycleId) {
                    vm.edit = true;

                    AppraisalCycle.find($scope.cycleId).then(function (cycle) {
                        vm.cycle = cycle;
                        vm.loaded.cycle = true;
                    });
                }
            }

            /**
             * Checks the validit of the form by applying custom rules and by
             * using built-in angular validation rules
             *
             * @return {boolean}
             */
            function isFormValid() {
                var startDate = moment(vm.cycle.cycle_start_date, 'DD/MM/YYYY');
                var endDate = moment(vm.cycle.cycle_end_date, 'DD/MM/YYYY');
                var selfDue = moment(vm.cycle.cycle_self_appraisal_due, 'DD/MM/YYYY');
                var managerDue = moment(vm.cycle.cycle_manager_appraisal_due, 'DD/MM/YYYY');
                var gradeDue = moment(vm.cycle.cycle_grade_due, 'DD/MM/YYYY');

                vm.form.cycle_start_date.$setValidity('startBeforeEnd', startDate.isBefore(endDate));
                vm.form.cycle_end_date.$setValidity('endAfterStart', endDate.isAfter(startDate));
                vm.form.cycle_manager_appraisal_due.$setValidity('managerAfterDue', managerDue.isAfter(selfDue));
                vm.form.cycle_grade_due.$setValidity('gradeAfterManager', gradeDue.isAfter(managerDue));

                return vm.form.$valid;
            }

            return vm;
        }
    ]);
});
