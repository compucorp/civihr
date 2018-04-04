/* eslint-env amd */

define([
  'common/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  RelationshipInstance.__name = 'RelationshipInstance';
  RelationshipInstance.$inject = ['ModelInstance'];

  instances.factory(RelationshipInstance.__name, RelationshipInstance);

  function RelationshipInstance (ModelInstance) {
    return ModelInstance.extend({});
  }
});
