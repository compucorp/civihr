define([
    'common/angular',
    'appraisals/modules/controllers'
], function (angular, controllers) {
    'use strict';

    controllers.controller('EditDatesModalCtrl', [
        '$filter', '$log', '$rootScope', '$scope', '$controller', '$uibModalInstance',
        'HR_settings', 'dialog',
        function ($filter, $log, $rootScope, $scope, $controller, $modalInstance, HR_settings, dialog) {
            $log.debug('EditDatesModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $uibModalInstance: $modalInstance
            }));
            var oldDueDates = {};

            vm.cycle = angular.copy($scope.cycle);
            vm.formErrors = {};
            vm.formSubmitted = false;

            /**
             * Submits the form
             */
            vm.submit = function () {
                vm.formSubmitted = true;

                if (!vm.form.$valid) {
                    vm.formErrors = formErrors();
                    return;
                }

                if (haveDueDatesChanged()) {
                    showDueDatesChangedDialog().then(function (response) {
                        !!response && updateDates();
                    });
                } else {
                    updateDates();
                }
            };

            init();

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
             */
            function init() {
                oldDueDates = vm.cycle.dueDates();
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

            /**
             * Submits the new dates
             */
            function updateDates() {
                vm.cycle.update().then(function () {
                    $rootScope.$emit('AppraisalCycle::edit', vm.cycle);
                    $modalInstance.close();
                });
            }

            return vm;
    }]);
});
