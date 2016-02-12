define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('ViewCycleModalCtrl', ['$log', '$controller', '$modalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('ViewCycleModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            return vm;
    }]);
});
