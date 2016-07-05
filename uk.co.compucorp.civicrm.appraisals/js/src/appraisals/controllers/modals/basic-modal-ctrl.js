define([
    'appraisals/modules/controllers'
], function (controllers) {
    controllers.controller('BasicModalCtrl',
        ['$log', '$uibModalInstance',
        function ($log, $modalInstance) {
            $log.debug('BasicModalCtrl');

            return {
                cancel: function () {
                    $modalInstance.dismiss('cancel');
                }
            };
        }
    ]);
});
