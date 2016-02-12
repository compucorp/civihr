define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('SendNotificationReminderModalCtrl', [
        '$log', '$controller', '$modal', '$modalInstance', '$rootElement',
        function ($log, $controller, $modal, $modalInstance, $rootElement) {
            $log.debug('SendNotificationReminderModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance
            }));

            /**
             * Opens the recipients list modal
             */
            vm.openNotificationRecipientsModal = function () {
                $modal.open({
                    targetDomEl: $rootElement.children().eq(0),
                    controller: 'NotificationRecipientsModalCtrl',
                    controllerAs: 'modal',
                    bindToController: true,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/notification-recipients.html'
                });
            };

            return vm;
    }]);
});
