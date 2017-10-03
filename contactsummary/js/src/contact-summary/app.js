/* eslint-env amd */

define([
  'common/angular',
  'contact-summary/modules/filters',
  'contact-summary/modules/services',
  'contact-summary/modules/settings',
  'contact-summary/controllers/contactSummary',
  'contact-summary/controllers/keyDates',
  'contact-summary/controllers/keyDetails',
  'contact-summary/components/leave-widget/leave-widget.component',
  'contact-summary/directives/donutChart'
], function (angular) {
  var app = angular.module('contactsummary', [
    'ngRoute',
    'ngResource',
    'ui.bootstrap',
    'common.services',
    'contactsummary.controllers',
    'contactsummary.components',
    'contactsummary.directives',
    'contactsummary.filters',
    'contactsummary.services',
    'contactsummary.settings'
  ]);

  app.config(['settings', '$routeProvider', '$resourceProvider', '$httpProvider', '$logProvider',
    function (settings, $routeProvider, $resourceProvider, $httpProvider, $logProvider) {
      $logProvider.debugEnabled(settings.debug);

      $routeProvider
        .when('/', {
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
