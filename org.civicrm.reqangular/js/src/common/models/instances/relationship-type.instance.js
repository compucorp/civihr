/* eslint-env amd */

define([
  'common/moment',
  'common/modules/models-instances',
  'common/models/instances/instance'
], function (moment, instances) {
  'use strict';

  instances.factory('RelationshipTypeInstance', RelationshipTypeInstance);

  RelationshipTypeInstance.$inject = ['ModelInstance'];

  function RelationshipTypeInstance (ModelInstance) {
    var extendedModelInstance = ModelInstance.extend({});

    return extendedModelInstance;
  }
});
