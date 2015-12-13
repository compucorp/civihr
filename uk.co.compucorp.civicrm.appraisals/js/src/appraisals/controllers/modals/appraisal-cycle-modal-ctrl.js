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
            vm.addCycle = function () {
                AppraisalCycle.create(vm.cycle).then(function (newCycle) {
                    $rootScope.$emit('AppraisalCycle::new', newCycle);
                    $modalInstance.close();
                });
            };

            init();

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
