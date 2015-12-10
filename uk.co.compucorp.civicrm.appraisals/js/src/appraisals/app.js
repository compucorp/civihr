define([
    'common/angular',
    'common/angularBootstrap',
    'appraisals/utils/routes',
    'appraisals/controllers/appraisals-ctrl',
    'appraisals/controllers/appraisals-dashboard-ctrl',
    'appraisals/controllers/modals/basic-modal-ctrl',
    'appraisals/controllers/modals/add-appraisal-cycle-modal-ctrl',
    'appraisals/directives/show-more',
    'appraisals/directives/grades-chart',
    'appraisals/models/appraisal-cycle',
    'appraisals/vendor/ui-router',
], function (angular, _, routes) {
    angular.module('appraisals', [
        'ngResource',
        'ui.router',
        'ui.bootstrap',
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
