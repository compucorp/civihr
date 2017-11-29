/* eslint-env amd */

define([
  'common/angular',
  'access-rights/controllers/access-rights.controller',
  'access-rights/controllers/access-rights-modal.controller'
], function (angular, AccessRightsCtrl, AccessRightsModalCtrl) {
  'use strict';

  return angular.module('access-rights.controllers', [])
    .controller(AccessRightsCtrl.__name, AccessRightsCtrl)
    .controller(AccessRightsModalCtrl.__name, AccessRightsModalCtrl);
});
