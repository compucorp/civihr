define([
    'common/angular',
    'common/angularBootstrap',
    'appraisals/controllers/appraisals-ctrl'
], function (angular) {
    angular.module('appraisals', [
        'ngRoute',
        'ngResource',
        'ui.bootstrap',
        'appraisals.controllers'
    ])
    .config(['$resourceProvider', '$httpProvider', '$logProvider',
        function ($resourceProvider, $httpProvider, $logProvider) {
            $logProvider.debugEnabled(true);
            $resourceProvider.defaults.stripTrailingSlashes = false;
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        }
    ])
    .run(['$log', function ($log) {
        $log.debug('app.run');
    }]);

    angular.bootstrap(document.querySelector('[data-appraisals-app]'), ['appraisals']);
});
