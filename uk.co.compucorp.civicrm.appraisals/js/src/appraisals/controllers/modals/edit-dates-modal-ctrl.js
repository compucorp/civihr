define([
    'common/angular',
    'appraisals/modules/controllers'
], function (angular, controllers) {
    'use strict';

    controllers.controller('EditDatesModalCtrl', [
        '$filter', '$log', '$rootScope', '$scope', '$controller', '$modalInstance',
        function ($filter, $log, $rootScope, $scope, $controller, $modalInstance) {
            $log.debug('EditDatesModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            vm.cycle = angular.copy($scope.cycle);
            vm.formErrors = {};
            vm.formSubmitted = false;

            /**
             * Submits the form
             */
            vm.submit = function () {
                vm.formSubmitted = true;

                formatDates();

                if (!vm.form.$valid) {
                    vm.formErrors = formErrors();
                    return;
                }

                vm.cycle.update().then(function () {
                    $rootScope.$emit('AppraisalCycle::edit', vm.cycle);
                    $modalInstance.close();
                });
            };

            /**
             * Formats all the dates in the current date format
             *
             * (Necessary because the date picker directives always return
             * a Date object instead of simply a string in the specified format)
             */
            function formatDates() {
                Object.keys(vm.cycle.dueDates()).forEach(function (key) {
                    vm.cycle[key] = $filter('date')(vm.cycle[key], 'dd/MM/yyyy');
                });
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

            return vm;
    }]);
});
