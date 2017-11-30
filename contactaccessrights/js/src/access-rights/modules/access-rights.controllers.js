/* eslint-env amd */

define([
  'common/angular',
  'access-rights/controllers/access-rights.controller',
  'access-rights/controllers/access-rights-modal.controller'
], function (angular, AccessRightsController, AccessRightsModalController) {
  'use strict';

  return angular.module('access-rights.controllers', [])
    .controller(AccessRightsController.__name, AccessRightsController)
    .controller(AccessRightsModalController.__name, AccessRightsModalController);
});
