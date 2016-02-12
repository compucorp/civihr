define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('NotificationRecipientsModalCtrl', ['$log', '$controller', '$modalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('NotificationRecipientsModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            return vm;
    }]);
});
