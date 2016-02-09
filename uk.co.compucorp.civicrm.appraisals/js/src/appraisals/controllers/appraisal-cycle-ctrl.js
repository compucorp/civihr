define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('AppraisalCycleCtrl', [
        '$log', '$modal', '$rootElement',
        function ($log, $modal, $rootElement) {
            $log.debug('AppraisalCycleCtrl');

            var vm = {};

            // dummy data
            vm.grades = [
                { label: "Value #1", value: 10 },
                { label: "Value #2", value: 20 },
                { label: "Value #3", value: 30 },
                { label: "Value #4", value: 40 }
            ];

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

            return vm;
        }
    ]);
});
