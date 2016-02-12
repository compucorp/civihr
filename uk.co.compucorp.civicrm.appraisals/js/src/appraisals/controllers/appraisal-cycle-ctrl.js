define([
    'common/lodash',
    'appraisals/modules/controllers'
], function (_, controllers) {
    'use strict';

    controllers.controller('AppraisalCycleCtrl', [
        '$log', '$modal', '$rootElement', 'dialog',
        function ($log, $modal, $rootElement, dialog) {
            $log.debug('AppraisalCycleCtrl');

            var vm = {};

            vm.filtersCollapsed = true;

            // dummy data
            vm.grades = [
                { label: "Value #1", value: 10 },
                { label: "Value #2", value: 20 },
                { label: "Value #3", value: 30 },
                { label: "Value #4", value: 40 }
            ];

            /**
             * Deletes a cycle (via a dialog modal)
             */
            vm.delete = function () {
                dialog.open({
                    title: 'Confirm Delete Appraisal',
                    copyCancel: 'Cancel',
                    copyConfirm: 'Delete',
                    classConfirm: 'btn-danger-outline',
                    msg: 'This cannot be undone'
                });
            };

            /**
             * Opens the Access Settings modal
             */
            vm.openAccessSettingsModal = function () {
                openModal({
                    controller: 'AccessSettingsModalCtrl',
                    templateFile: 'access-settings.html'
                });
            };

            /**
             * Opens the Add Contacts modal
             */
            vm.openAddContactsModal = function () {
                openModal({
                    controller: 'AddContactsModalCtrl',
                    templateFile: 'add-contacts.html'
                });
            };

            /**
             * Opens the Edit Dates modal
             */
            vm.openEditDatesModal = function () {
                openModal({
                    controller: 'EditDatesModalCtrl',
                    templateFile: 'edit-dates.html'
                });
            };

            /**
             * Opens the View Cycle modal
             */
            vm.openViewCycleModal = function () {
                openModal({
                    controller: 'ViewCycleModalCtrl',
                    templateFile: 'view-cycle.html'
                });
            };

            /**
             * Opens the Send Notification Reminder modal
             */
            vm.openSendNotificationReminderModal = function () {
                openModal({
                    controller: 'SendNotificationReminderModalCtrl',
                    windowClass: 'modal--send-notification-reminder',
                    templateFile: 'send-notification-reminder.html',
                });
            };

            /**
             * Opens a modal
             *
             * @param {object} options - Parameter for the modal
             * @return {Promise}
             */
            function openModal(options) {
                return $modal.open(_.assign({
                    targetDomEl: $rootElement.children().eq(0),
                    controller: options.controller,
                    controllerAs: 'modal',
                    bindToController: true,
                    windowClass: options.windowClass || null,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/' + options.templateFile
                }));
            }

            return vm;
        }
    ]);
});
