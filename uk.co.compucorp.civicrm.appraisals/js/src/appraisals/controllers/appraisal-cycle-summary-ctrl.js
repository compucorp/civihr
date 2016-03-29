define([
    'common/lodash',
    'appraisals/modules/controllers'
], function (_, controllers) {
    'use strict';

    controllers.controller('AppraisalCycleSummaryCtrl', [
        '$log', '$modal', '$rootElement', '$rootScope', '$scope', '$timeout',
        function ($log, $modal, $rootElement, $rootScope, $scope, $timeout) {
            $log.debug('AppraisalCycleSummaryCtrl');

            var vm = {};

            vm.picker = { opened: false };
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
             * Opens the Edit Dates modal
             */
            vm.openEditDatesModal = function () {
                openModal({
                    controller: 'EditDatesModalCtrl',
                    templateFile: 'edit-dates.html',
                    scopeData: {
                        cycle: $scope.cycle.cycle
                    }
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
                    scope: (function (scopeData) {
                        var modalScope = $rootScope.$new();
                        _.assign(modalScope, scopeData);

                        return modalScope;
                    })(options.scopeData),
                    windowClass: options.windowClass || null,
                    templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/' + options.templateFile
                }));
            }

            return vm;
        }
    ]);
});
