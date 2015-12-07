define([
    'common/angular',
    'appraisals/modules/controllers'
], function (angular, controllers) {
    controllers.controller('AddAppraisalCycleModalCtrl',
        ['$log', '$controller', '$modalInstance',
        function ($log, $controller, $modalInstance) {
            $log.debug('AddAppraisalCycleModalCtrl');

            return angular.extend(
                Object.create($controller('BasicModalCtrl', {
                    $modalInstance: $modalInstance
                })), {
                    calendarOpen: false,
                    date: new Date(),
                    openCalendar: function () {
                        this.calendarOpen = !this.calendarOpen;
                    }
                }
            );
        }
    ]);
});
