define([
    'common/lodash',
    'appraisals/modules/controllers',
    'appraisals/models/appraisal-cycle'
], function (_, controllers) {
    'use strict';

    controllers.controller('AppraisalCycleAppraisalsCtrl', [
        '$log', 'departments', 'levels', 'locations', 'regions',
        function ($log, departments, levels, locations, regions) {
            $log.debug('AppraisalCycleAppraisalsCtrl');

            var vm = {};

            vm.filtersCollapsed = true;

            vm.departments = departments;
            vm.levels = levels;
            vm.locations = locations;
            vm.regions = regions;

            return vm;
        }
    ]);
});
