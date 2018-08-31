/* eslint-env amd */

define([
  'common/angular',
  'access-rights/controllers/access-rights.controller',
  'access-rights/controllers/access-rights-modal.controller',
  'access-rights/access-rights.config',
  'access-rights/access-rights.core',
  'access-rights/access-rights.models',
  'access-rights/access-rights.run'
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
