define([
    'common/angular',
    'appraisals/modules/controllers'
], function (angular, controllers) {
    controllers.controller('AddAppraisalCycleModalCtrl',
        ['$log', '$rootScope', '$controller', '$modalInstance', 'AppraisalCycle', 'types',
        function ($log, $rootScope, $controller, $modalInstance, AppraisalCycle, types) {
            $log.debug('AddAppraisalCycleModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            vm.newCycle = {};
            vm.types = types;

            /**
             * Adds the new cycle and on complete emits an event, closes the modal
             */
            vm.addCycle = function () {
                AppraisalCycle.create(vm.newCycle).then(function (newCycle) {
                    $rootScope.$emit('AppraisalCycle::new', newCycle);
                    $modalInstance.close();
                });
            };

            return vm;
        }
    ]);
});
