/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/contact-instance'
], function (_) {
  'use strict';

  describe('ContactInstance', function () {
    var ContactInstance, ModelInstance;

    beforeEach(module('common.models.instances'));
    beforeEach(inject(function (_ContactInstance_, _ModelInstance_) {
      ContactInstance = _ContactInstance_;
      ModelInstance = _ModelInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(ContactInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });
  });
});
