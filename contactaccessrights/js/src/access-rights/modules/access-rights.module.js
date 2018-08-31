/* eslint-env amd */

define([
  'common/angular',
  'access-rights/controllers/access-rights.controller',
  'access-rights/controllers/access-rights-modal.controller',
  'access-rights/modules/access-rights.config',
  'access-rights/modules/access-rights.core',
  'access-rights/modules/access-rights.models',
  'access-rights/modules/access-rights.run'
], function (angular, AccessRightsController, AccessRightsModalController) {
  'use strict';

  angular.module('access-rights', [
    'access-rights.core',
    'access-rights.config',
    'access-rights.run',
    'access-rights.models'
  ])
    .controller(AccessRightsController)
    .controller(AccessRightsModalController);

  return angular;
});
