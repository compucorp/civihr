define([
  'common/angular',
  'common/ui-select',
  'common/angularBootstrap',
  'common/modules/xeditable-civi',
  'common/directives/loading',
  'access-rights/controllers/access-rights.controller',
  'access-rights/controllers/access-rights-modal.controller',
  'access-rights/models/region.model',
  'access-rights/models/location.model',
  'access-rights/models/right.model'
], function (angular) {
  'use strict'

  angular.module('access-rights', [
      'ngAnimate',
      'ui.bootstrap',
      'ui.select',
      'common.directives',
      'xeditable-civi',
      'access-rights.controllers',
      'access-rights.models'
    ])
    .run(['$log', 'editableOptions', 'editableThemes',
      function ($log, editableOptions, editableThemes) {
        $log.debug('app.run');
        editableOptions.theme = 'bs3';
      }
    ])
    .config(['$locationProvider', '$httpProvider', function ($locationProvider, $httpProvider) {
      $locationProvider.html5Mode({
        enabled: true,
        requireBase: false
      });
      $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    }]);

  return angular;
});
