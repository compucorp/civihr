define([
    'appraisals/utils/routes',
    'common/angular',
    'common/angularBootstrap',
    'common/services/dialog',
    'common/directives/loading',
    'appraisals/controllers/appraisals-ctrl',
    'appraisals/controllers/appraisals-dashboard-ctrl',
    'appraisals/controllers/appraisal-cycle-ctrl',
    'appraisals/controllers/modals/basic-modal-ctrl',
    'appraisals/controllers/modals/appraisal-cycle-modal-ctrl',
    'appraisals/directives/show-more',
    'appraisals/directives/grades-chart',
    'appraisals/models/appraisal-cycle',
    'appraisals/models/instances/appraisal-cycle-instance',
    'appraisals/vendor/ui-router',
], function (routes, angular) {
    angular.module('appraisals', [
        'ngResource',
        'ui.router',
        'ui.bootstrap',
        'common.dialog',
        'common.directives',
        'appraisals.controllers',
        'appraisals.directives',
        'appraisals.models'
    ])
    .config(['$stateProvider', '$urlRouterProvider', '$resourceProvider', '$httpProvider', '$logProvider',
        function ($stateProvider, $urlRouterProvider, $resourceProvider, $httpProvider, $logProvider) {
            $logProvider.debugEnabled(true);
            $resourceProvider.defaults.stripTrailingSlashes = false;
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

            routes($urlRouterProvider, $stateProvider);
        }
    ])
    .run(['$log', function ($log) {
        $log.debug('app.run');
    }]);

    return angular;
});
