define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('EditDatesModalCtrl', ['$log', '$controller', '$modalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('AppraisalCycleModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            return vm;
    }]);
});
