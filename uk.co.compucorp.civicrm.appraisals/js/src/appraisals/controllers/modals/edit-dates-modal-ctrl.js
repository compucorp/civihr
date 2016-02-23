define([
    'common/lodash',
    'appraisals/modules/controllers'
], function (_, controllers) {
    'use strict';

    controllers.controller('EditDatesModalCtrl', ['$log', '$scope', '$controller', '$modalInstance',
        function ($log, $scope, $controller, $modalInstance) {
            $log.debug('EditDatesModalCtrl');

            var vm = _.assign(Object.create($controller('BasicModalCtrl', {
                $modalInstance: $modalInstance,
                $scope: $scope
            })), {
                cycle: $scope.cycle
            });

            return vm;
    }]);
});
