define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('SendNotificationReminderModalCtrl', ['$log', '$controller', '$modalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('SendNotificationReminderModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            return vm;
    }]);
});
