define([
    'appraisals/modules/controllers'
], function (controllers) {
    controllers.controller('AppraisalsDashboardCtrl',
        ['$log', function ($log) {
        $log.debug('AppraisalsDashboardCtrl');

        return {
            filtersCollapsed: true,
            data: [ // mock data
                { label: 1, value: 17 },
                { label: 2, value: 74 },
                { label: 3, value: 90 },
                { label: 4, value: 30 }
            ]
        }
    }]);
});
