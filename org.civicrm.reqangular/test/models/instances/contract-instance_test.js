/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/contract-instance'
], function (_) {
  'use strict';

  describe('ContractInstance', function () {
    var ContractInstance, ModelInstance;

    beforeEach(module('common.models.instances'));

    beforeEach(inject(['ContractInstance', 'ModelInstance',
      function (_ContractInstance_, _ModelInstance_) {
        ContractInstance = _ContractInstance_;
        ModelInstance = _ModelInstance_;
      }]));

    it('inherits from ModelInstance', function () {
      expect(_.functions(ContractInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });
  });
});
