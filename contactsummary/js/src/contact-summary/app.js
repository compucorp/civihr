define([
    'common/angular',
    'contact-summary/modules/contact-summary.filters',
    'contact-summary/modules/contact-summary.services',
    'contact-summary/modules/contact-summary.settings',
    'contact-summary/controllers/contact-summary.controller',
    'contact-summary/controllers/key-dates.controller',
    'contact-summary/controllers/key-details.controller',
    'contact-summary/directives/donut-chart.directive'
], function (angular) {
    var app = angular.module('contactsummary', [
        'ngRoute',
        'ngResource',
        'ui.bootstrap',
        'common.services',
        'contactsummary.controllers',
        'contactsummary.directives',
        'contactsummary.filters',
        'contactsummary.services',
        'contactsummary.settings'
    ]);

    app.config(['settings', '$routeProvider', '$resourceProvider', '$httpProvider', '$logProvider',
        function (settings, $routeProvider, $resourceProvider, $httpProvider, $logProvider) {
            $logProvider.debugEnabled(settings.debug);

            $routeProvider.
                when('/', {
                    controller: 'ContactSummaryCtrl',
                    controllerAs: 'ContactSummaryCtrl',
                    templateUrl: settings.pathBaseUrl + settings.pathTpl + 'mainTemplate.html',
                    resolve: {}
                }
            ).otherwise({redirectTo: '/'});

            $resourceProvider.defaults.stripTrailingSlashes = false;

            $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        }
    ]);

    app.run(['settings', '$rootScope', '$q', '$log',
        function (settings, $rootScope, $q, $log) {
            $log.debug('app.run');

            $rootScope.pathTpl = settings.pathTpl;
            $rootScope.prefix = settings.classNamePrefix;
        }
    ]);
});
