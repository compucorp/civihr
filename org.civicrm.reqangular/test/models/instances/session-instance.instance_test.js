/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/session-instance.instance'
], function (_) {
  'use strict';

  describe('sessionInstance', function () {
    var SessionInstance, ModelInstance;

    beforeEach(module('common.models.instances'));
    beforeEach(inject(function (_SessionInstance_, _ModelInstance_) {
      SessionInstance = _SessionInstance_;
      ModelInstance = _ModelInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(SessionInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });
  });
});
