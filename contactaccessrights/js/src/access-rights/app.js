define([
  'common/angular',
  'common/ui-select',
  'common/directives/loading',
  'access-rights/controllers/access-rights-ctrl',
  'access-rights/controllers/access-rights-modal-ctrl',
  'access-rights/models/region',
  'access-rights/models/location',
  'access-rights/models/right'
], function (angular) {
  angular.module('access-rights', [
      'access-rights.controllers',
      'access-rights.models',
      'ui.select',
      'xeditable-civi'
    ])
    .run(['$log', 'editableOptions', 'editableThemes',
      function ($log, editableOptions, editableThemes) {
        $log.debug('app.run');
        editableOptions.theme = 'bs3';
      }
    ]).config(['$locationProvider', function ($locationProvider) {
      $locationProvider.html5Mode({
        enabled: true,
        requireBase: false
      });
    }]);

  return angular;
});
