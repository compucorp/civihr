define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('ViewCycleModalCtrl', ['$log', '$controller', '$uibModalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('ViewCycleModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $uibModalInstance: $modalInstance
            }));

            return vm;
    }]);
});
