define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('NotificationRecipientsModalCtrl', ['$log', '$controller', '$uibModalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('NotificationRecipientsModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $uibModalInstance: $modalInstance
            }));

            return vm;
    }]);
});
