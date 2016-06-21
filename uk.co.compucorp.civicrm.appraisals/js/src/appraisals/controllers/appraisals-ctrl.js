define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('AppraisalsCtrl',
        ['$log', '$rootElement', '$uibModal', 'AppraisalCycle',
        function ($log, $rootElement, $modal, AppraisalCycle) {
            $log.debug('AppraisalsCtrl');

            return {
                openAppraisalCycleModal: function () {
                    $modal.open({
                        appendTo: $rootElement.children().eq(0),
                        controller: 'AppraisalCycleModalCtrl',
                        controllerAs: 'modal',
                        bindToController: true,
                        templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/appraisal-cycle.html'
                    });
                }
            }
        }
    ]);
});
