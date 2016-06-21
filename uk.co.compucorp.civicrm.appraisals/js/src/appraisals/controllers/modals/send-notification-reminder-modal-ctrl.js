define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('SendNotificationReminderModalCtrl', [
        '$log', '$controller', '$uibModal', '$uibModalInstance', '$rootElement',
        function ($log, $controller, $modal, $modalInstance, $rootElement) {
            $log.debug('SendNotificationReminderModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $uibModalInstance: $modalInstance
            }));

            /**
             * Opens the recipients list modal
             */
            vm.openNotificationRecipientsModal = function () {
                $modal.open({
                    appendTo: $rootElement.children().eq(0),
                    controller: 'NotificationRecipientsModalCtrl',
                    controllerAs: 'modal',
                    bindToController: true,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/notification-recipients.html'
                });
            };

            return vm;
    }]);
});
