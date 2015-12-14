define([
    'common/angular',
    'appraisals/modules/controllers'
], function (angular, controllers) {
    controllers.controller('AppraisalCycleModalCtrl',
        ['$log', '$rootScope', '$scope', '$controller', '$modalInstance', 'AppraisalCycle',
        function ($log, $rootScope, $scope, $controller, $modalInstance, AppraisalCycle) {
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
                AppraisalCycle.update(vm.cycle.id, vm.cycle).then(function (cycle) {
                    $rootScope.$emit('AppraisalCycle::edit', cycle);
                    $modalInstance.close();
                });
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
