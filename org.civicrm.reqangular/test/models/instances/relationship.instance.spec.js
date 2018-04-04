/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/relationship.instance'
], function (_) {
  'use strict';

  describe('RelationshipInstance', function () {
    var ModelInstance, RelationshipInstance;

    beforeEach(module('common.models.instances'));
    beforeEach(inject(function (_ModelInstance_, _RelationshipInstance_) {
      ModelInstance = _ModelInstance_;
      RelationshipInstance = _RelationshipInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(RelationshipInstance))
        .toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });
  });
});
