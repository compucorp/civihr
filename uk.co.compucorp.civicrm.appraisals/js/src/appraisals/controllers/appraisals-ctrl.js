define([
    'appraisals/modules/controllers'
], function (controllers) {
    controllers.controller('AppraisalsCtrl',
        ['$log', '$rootElement', '$modal', 'AppraisalCycle',
        function ($log, $rootElement, $modal, AppraisalCycle) {
            $log.debug('AppraisalsCtrl');

            return {
                openAppraisalCycleModal: function () {
                    $modal.open({
                        targetDomEl: $rootElement.children().eq(0),
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
