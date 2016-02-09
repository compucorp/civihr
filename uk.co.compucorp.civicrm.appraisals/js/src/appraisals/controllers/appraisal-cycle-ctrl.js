define([
    'appraisals/modules/controllers'
], function (controllers) {
    'use strict';

    controllers.controller('AppraisalCycleCtrl', [
        '$log',
        function ($log) {
            $log.debug('AppraisalCycleCtrl');

            var vm = {};

            // dummy data
            vm.grades = [
                { label: "Value #1", value: 10 },
                { label: "Value #2", value: 20 },
                { label: "Value #3", value: 30 },
                { label: "Value #4", value: 40 }
            ];

            return vm;
        }
    ]);
});
