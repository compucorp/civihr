define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('AddContactsModalCtrl', ['$log', '$controller', '$modalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('AddContactsModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            return vm;
    }]);
});
