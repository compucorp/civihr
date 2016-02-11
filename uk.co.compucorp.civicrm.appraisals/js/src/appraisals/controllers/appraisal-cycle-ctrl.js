define([
    'appraisals/modules/controllers'
], function (controllers) {
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
                    msg: 'This cannot be undone'
                });
            };

            /**
             * Opens the Access Settings modal
             */
            vm.openAccessSettingsModal = function () {
                $modal.open({
                    targetDomEl: $rootElement.children().eq(0),
                    controller: 'AccessSettingsModalCtrl',
                    controllerAs: 'modal',
                    bindToController: true,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/access-settings.html'
                });
            };

            /**
             * Opens the Add Contacts modal
             */
            vm.openAddContactsModal = function () {
                $modal.open({
                    targetDomEl: $rootElement.children().eq(0),
                    controller: 'AddContactsModalCtrl',
                    controllerAs: 'modal',
                    bindToController: true,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/add-contacts.html'
                });
            };

            /**
             * Opens the Edit Dates modal
             */
            vm.openEditDatesModal = function () {
                $modal.open({
                    targetDomEl: $rootElement.children().eq(0),
                    controller: 'EditDatesModalCtrl',
                    controllerAs: 'modal',
                    bindToController: true,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/edit-dates.html'
                });
            };

            /**
             * Opens the View Cycle modal
             */
            vm.openViewCycleModal = function () {
                $modal.open({
                    targetDomEl: $rootElement.children().eq(0),
                    controller: 'ViewCycleModalCtrl',
                    controllerAs: 'modal',
                    bindToController: true,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/view-cycle.html'
                });
            };

            return vm;
        }
    ]);
});
