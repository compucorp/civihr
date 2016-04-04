define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('AccessSettingsModalCtrl', ['$log', '$controller', '$modalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('AccessSettingsModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            return vm;
    }]);
});
