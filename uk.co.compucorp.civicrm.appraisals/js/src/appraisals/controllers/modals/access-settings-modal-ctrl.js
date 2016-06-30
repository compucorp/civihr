define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('AccessSettingsModalCtrl', ['$log', '$controller', '$uibModalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('AccessSettingsModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $uibModalInstance: $modalInstance
            }));

            return vm;
    }]);
});
