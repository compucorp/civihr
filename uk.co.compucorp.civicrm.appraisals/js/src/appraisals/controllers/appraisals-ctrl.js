define([
    'appraisals/modules/controllers'
], function (controllers) {
    controllers.controller('AppraisalsCtrl',
        ['$log', '$rootElement', '$modal',
        function ($log, $rootElement, $modal) {
            $log.debug('AppraisalsCtrl');

            return {
                openAddAppraisalCycleModal: function () {
                    $modal.open({
                        targetDomEl: $rootElement,
                        controller: 'AddAppraisalCycleModalCtrl',
                        controllerAs: 'modal',
                        bindToController: true,
                        templateUrl: CRM.vars.appraisals.baseURL + '/views/modals/add-appraisal-cycle.html'
                    });
                },
            }
        }
    ]);
});
