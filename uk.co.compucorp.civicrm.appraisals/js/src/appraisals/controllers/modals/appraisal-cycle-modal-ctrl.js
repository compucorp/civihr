define([
    'common/lodash',
    'common/moment',
    'appraisals/modules/controllers'
], function (_, moment, controllers) {
    'use strict';

    controllers.controller('AppraisalCycleModalCtrl',
        ['$filter', '$log', '$rootScope', '$scope', '$controller', '$modalInstance',
        'AppraisalCycle', 'HR_settings', 'dialog',
        function ($filter, $log, $rootScope, $scope, $controller, $modalInstance, AppraisalCycle, HR_settings, dialog) {
            $log.debug('AppraisalCycleModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));
            var oldDueDates = {};

            vm.cycle = {};
            vm.types = [];

            vm.edit = false;
            vm.formErrors = {};
            vm.formSubmitted = false;
            vm.loaded = {
                types: false,
                cycle: !$scope.cycleId
            };

            /**
             * If the form is valid, it creates/edit the cycle and on
             * complete emits an event, closes the modal
             *
             * If the form is not valid, it fetches the form errors to display
             */
            vm.submit = function () {
                vm.formSubmitted = true;

                formatDates();

                if (!isFormValid()) {
                    vm.formErrors = formErrors();
                    return;
                }

                if (vm.edit) {
                    // If the due dates have been changed, it wait for
                    // user's manual confirmation before performing the edit
                    if (haveDueDatesChanged()) {
                        showDueDatesChangedDialog().then(function (response) {
                            !!response && editCycle();
                        });
                    } else {
                        editCycle();
                    }
                } else {
                    createCycle();
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
                        vm.cycle[key] = $filter('date')(vm.cycle[key], HR_settings.DATE_FORMAT);
                    }
                }
            }

            /**
             * Extracts from the AngularJS form object all the current errors
             * for each field
             *
             * @return {object} contains the errors grouped by field:
             *   {
             *     field_1: { error_1: true, error_2: true },
             *     // ..
             *     field_N: { error_1: true }
             *   }
             */
            function formErrors() {
                return _(vm.form)
                    // filters out internal properties of the form object
                    .omit(function (value, key) {
                        return _.startsWith(key, '$');
                    })
                    // creates an object which groups the errors by field
                    .transform(function (result, value, key) {
                        result[key] = _.omit(value.$error, function (value) {
                            return !value
                        });
                        return result;
                    }, {})
                    // filters out fields with no errors
                    .omit(function (value) {
                        return _.isEmpty(value);
                    })
                    .value()
            }

            /**
             * Compares the old due dates with the new due dates
             *
             * @return {boolean}
             */
            function haveDueDatesChanged() {
                return !_.isEqual(oldDueDates, vm.cycle.dueDates());
            }

            /**
             * Initialization code
             *
             * If there is a cycle id passed in the scope, it sets the modal
             * in edit mode and retrieves the data of the cycle, storing
             * its due dates for future comparison on submit
             */
            function init() {
                AppraisalCycle.types().then(function (types) {
                    vm.types = types;
                    vm.loaded.types = true;
                });

                if ($scope.cycleId) {
                    vm.edit = true;

                    AppraisalCycle.find($scope.cycleId).then(function (cycle) {
                        oldDueDates = cycle.dueDates();

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
                var momentFormat = HR_settings.DATE_FORMAT.toUpperCase();

                var startDate = moment(vm.cycle.cycle_start_date, momentFormat);
                var endDate = moment(vm.cycle.cycle_end_date, momentFormat);
                var selfDue = moment(vm.cycle.cycle_self_appraisal_due, momentFormat);
                var managerDue = moment(vm.cycle.cycle_manager_appraisal_due, momentFormat);
                var gradeDue = moment(vm.cycle.cycle_grade_due, momentFormat);

                vm.form.cycle_end_date.$setValidity('isAfter', endDate.isAfter(startDate));
                vm.form.cycle_manager_appraisal_due.$setValidity('isAfter', managerDue.isAfter(selfDue));
                vm.form.cycle_grade_due.$setValidity('isAfter', gradeDue.isAfter(managerDue));

                return vm.form.$valid;
            }

            /**
             * Shows the dialog warning the user that due dates have changed
             *
             * @return {Promise}
             */
            function showDueDatesChangedDialog() {
                return dialog.open({
                    title: 'Confirm Change Dates',
                    copyCancel: 'Cancel',
                    copyConfirm: 'Proceed',
                    msg: 'This will update the due dates for all appraisals in the cycle'
                });
            }

            return vm;
        }
    ]);
});
