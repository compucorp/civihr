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
                $modalInstance: $modalInstance,
                $scope: $scope
            }));

            vm.cycle = angular.copy($scope.cycle);

            /**
             * Submits the form
             */
            vm.submit = function () {
                formatDates();

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

            return vm;
    }]);
});
