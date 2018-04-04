/* eslint-env amd */

define([
  'common/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('RelationshipInstance', RelationshipInstance);

  RelationshipInstance.__name = 'RelationshipInstance';
  RelationshipInstance.$inject = ['ModelInstance'];

  function RelationshipInstance (ModelInstance) {
    return ModelInstance.extend({});
  }
});
