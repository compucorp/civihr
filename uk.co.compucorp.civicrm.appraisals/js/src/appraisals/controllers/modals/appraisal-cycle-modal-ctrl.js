define([
    'common/lodash',
    'appraisals/modules/controllers'
], function (_, controllers) {
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
            vm.loaded = {
                types: false,
                cycle: !$scope.cycleId
            };

            /**
             * Adds the new cycle and on complete emits an event, closes the modal
             */
            vm.submit = function () {
                formatDates();

                if (vm.edit) {
                    editCycle();
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
             * Formats all the selecte dates in the correct date format
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

            return vm;
        }
    ]);
});
