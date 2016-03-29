define([
    'common/lodash',
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (_, controllers) {
    'use strict';

    controllers.controller('AppraisalCycleCtrl', [
        '$log', '$modal', '$rootElement', '$rootScope', '$stateParams', '$timeout',
        'AppraisalCycle', 'dialog', 'departments', 'levels', 'locations', 'regions', 'statuses', 'types',
        function ($log, $modal, $rootElement, $rootScope, $stateParams, $timeout, AppraisalCycle, dialog, departments, levels, locations, regions, statuses, types) {
            $log.debug('AppraisalCycleCtrl');

            var vm = {};
            var cachedAttributes = {};

            vm.cycle = {};
            vm.filtersCollapsed = true;
            vm.loading = { cycle: true, appraisals: true };
            vm.picker = { opened: false };

            vm.departments = departments;
            vm.levels = levels;
            vm.locations = locations;
            vm.regions = regions;
            vm.statuses = statuses;
            vm.types = types;

            // dummy data
            vm.grades = [
                { label: "Value #1", value: 10 },
                { label: "Value #2", value: 20 },
                { label: "Value #3", value: 30 },
                { label: "Value #4", value: 40 }
            ];

            /**
             *  editable-bsdate does not work with the latest ui.bootstrap
             *  (https://github.com/vitalets/angular-xeditable/issues/164)
             *
             * This method provides a workaround for the issue
             */
            vm.toggleCalendar = function () {
                $timeout(function () {
                    vm.picker.opened = !vm.picker.opened;
                });
            };

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
                    templateFile: 'add-contacts.html',
                    scopeData: {
                        cycleId: vm.cycle.id
                    }
                });
            };

            /**
             * Opens the Edit Dates modal
             */
            vm.openEditDatesModal = function () {
                openModal({
                    controller: 'EditDatesModalCtrl',
                    templateFile: 'edit-dates.html',
                    scopeData: {
                        cycle: vm.cycle
                    }
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
             * Shows a confirmation dialog
             * If confirmed, updates the cycle. If rejected, restores the old values
             */
            vm.update = function () {
                dialog.open({
                    title: 'Confirm Cycle Update',
                    copyCancel: null,
                    copyConfirm: 'Proceed',
                    msg: 'This will update the data of the cycle'
                })
                .then(function (response) {
                    if (response) {
                        vm.cycle.update();
                        cacheAttributes();
                    } else {
                        _.assign(vm.cycle, cachedAttributes);
                    }
                });
            }

            init();

            /**
             * Attaches the listeners to the $rootScope
             */
            function addListeners() {
                $rootScope.$on('AppraisalCycle::edit', function (event, editedCycle) {
                    _.assign(vm.cycle, editedCycle);
                    cacheAttributes();
                });
            }

            /**
             * Caches the cycle attributes so that they can be restored
             * in case the user won't confirm a future cycle's data update
             */
            function cacheAttributes() {
                cachedAttributes = vm.cycle.attributes();
            }

            /**
             * Initializes the listeners and cycle data
             */
            function init() {
                addListeners();
                loadCycleData();
            }

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
                    scope: (function (scopeData) {
                        var modalScope = $rootScope.$new();
                        _.assign(modalScope, scopeData);

                        return modalScope;
                    })(options.scopeData),
                    windowClass: options.windowClass || null,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/' + options.templateFile
                }));
            }

            /**
             * Loads the cycle data and its overdue appraisals
             */
            function loadCycleData() {
                AppraisalCycle.find($stateParams.cycleId).then(function (cycle) {
                    vm.cycle = cycle;
                    vm.loading.cycle = false;

                    return vm.cycle.loadAppraisals({ overdue: true });
                })
                .then(function () {
                    cacheAttributes();
                    vm.loading.appraisals = false;
                });
            }

            return vm;
        }
    ]);
});
