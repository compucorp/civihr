define([
  'common/angular',
  'common/ui-select',
  'common/modules/xeditable-civi',
  'common/directives/loading',
  'access-rights/controllers/access-rights-ctrl',
  'access-rights/controllers/access-rights-modal-ctrl',
  'access-rights/models/region',
  'access-rights/models/location',
  'access-rights/models/right'
], function (angular) {
  'use strict'

  angular.module('access-rights', [
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
