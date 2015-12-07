define([
    'common/angular',
    'common/angularBootstrap',
    'appraisals/utils/routes',
    'appraisals/controllers/appraisals-ctrl',
    'appraisals/controllers/appraisals-dashboard-ctrl',
    'appraisals/vendor/ui-router',
], function (angular, _, routes) {
    angular.module('appraisals', [
        'ngResource',
        'ui.router',
        'ui.bootstrap',
        'appraisals.controllers'
    ])
    .config(['$stateProvider', '$urlRouterProvider', '$resourceProvider', '$httpProvider', '$logProvider',
        function ($stateProvider, $urlRouterProvider, $resourceProvider, $httpProvider, $logProvider) {
            $logProvider.debugEnabled(true);
            $resourceProvider.defaults.stripTrailingSlashes = false;
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

            routes.addRoutes($urlRouterProvider, $stateProvider);
        }
    ])
    .run(['$log', function ($log) {
        $log.debug('app.run');
    }]);

    angular.bootstrap(document.querySelector('[data-appraisals-app]'), ['appraisals']);
});
