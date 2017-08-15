/* eslint-env amd */
/* globals location */

define([
  'common/angular',
  'common/ui-select',
  'common/directives/angular-date/date-input',
  'common/modules/directives',
  'common/modules/routers/compu-ng-route',
  'common/services/angular-date/date-format',
  'common/services/dom-event-trigger',
  'job-roles/vendor/angular-editable',
  'job-roles/vendor/angular-filter',
  'job-roles/controllers/job-roles.controller',
  'job-roles/controllers/modal-dialog.controller',
  'job-roles/services/date-validation.service',
  'job-roles/services/filters.service',
  'job-roles/services/job-role.service'
], function (angular) {
  'use strict';

  angular.module('hrjobroles', [
    'angular.filter',
    'ngAnimate',
    'ngSanitize',
    'ngResource',
    'ui.bootstrap',
    'ui.select',
    'xeditable',
    'common.angularDate',
    'common.directives',
    'common.services',
    'compuNgRoute',
    'hrjobroles.controllers',
    'hrjobroles.filters',
    'hrjobroles.services'
  ])
  .constant('settings', {
    classNamePrefix: 'hrjobroles-',
    contactId: decodeURIComponent((new RegExp('[?|&]cid=([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null,
    debug: true,
    pathApp: CRM.vars.hrjobroles.path,
    pathRest: CRM.url('civicrm/ajax/rest'),
    pathBaseUrl: CRM.vars.hrjobroles.baseURL + '/',
    pathTpl: 'views/',
    pathIncludeTpl: 'views/include/'
  })
  .config(['settings', '$routeProvider', '$resourceProvider', '$httpProvider', '$logProvider',
    function (settings, $routeProvider, $resourceProvider, $httpProvider, $logProvider) {
      $logProvider.debugEnabled(settings.debug);

      $routeProvider
        .resolveForAll({
          format: ['DateFormat', function (DateFormat) {
            return DateFormat.getDateFormat();
          }]
        })
        .when('/', {
          templateUrl: settings.pathBaseUrl + settings.pathTpl + 'mainTemplate.html?v=1',
          resolve: {},
          controller: 'JobRolesController',
          controllerAs: 'jobroles'
        })
        .otherwise({redirectTo: '/'});

      $resourceProvider.defaults.stripTrailingSlashes = false;
      $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    }
  ])
  .run(['settings', '$rootScope', '$q', '$log', 'editableOptions',
    function (settings, $rootScope, $q, $log, editableOptions) {
      $log.debug('app.run');

      // Set bootstrap 3 as default theme
      editableOptions.theme = 'bs3';

      // Pass the values from our settings
      $rootScope.contactId = settings.contactId;
      $rootScope.pathBaseUrl = settings.pathBaseUrl;
      $rootScope.pathTpl = settings.pathTpl;
      $rootScope.pathIncludeTpl = settings.pathIncludeTpl;
      $rootScope.prefix = settings.classNamePrefix;
    }
  ]);
});
