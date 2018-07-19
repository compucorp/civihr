/* eslint-env amd */

define([
  'common/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('ContactJobRoleInstance', ['ModelInstance',
    function (ModelInstance) {
      return ModelInstance.extend({});
    }]);
});
