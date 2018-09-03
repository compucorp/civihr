/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/relationship-type.instance'
], function (_) {
  'use strict';

  describe('RelationshipTypeInstance', function () {
    var RelationshipTypeInstance, ModelInstance;

    beforeEach(module('common.models.instances'));

    beforeEach(inject(['RelationshipTypeInstance', 'ModelInstance',
      function (_RelationshipTypeInstance_, _ModelInstance_) {
        RelationshipTypeInstance = _RelationshipTypeInstance_;
        ModelInstance = _ModelInstance_;
      }]));

    it('inherits from ModelInstance', function () {
      expect(_.functionsIn(RelationshipTypeInstance)).toEqual(jasmine.arrayContaining(_.functionsIn(ModelInstance)));
    });
  });
});
