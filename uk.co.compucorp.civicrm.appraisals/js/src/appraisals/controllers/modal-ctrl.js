define([
    'appraisals/modules/controllers'
], function (controllers) {
    controllers.controller('ModalCtrl',
        ['$log', '$modalInstance',
        function ($log, $modalInstance) {
            $log.debug('ModalCtrl');

            return {
                cancel: function () {
                    $modalInstance.dismiss('cancel');
                }
            };
        }
    ]);
});
