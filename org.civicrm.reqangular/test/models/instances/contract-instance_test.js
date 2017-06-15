/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/contract-instance'
], function (_) {
  'use strict';

  describe('ContractInstance', function () {
    var ContractInstance, ModelInstance, ContractAPI;

    beforeEach(module('common.models.instances'));
    beforeEach(inject(['api.contract', 'ContractInstance', 'ModelInstance',
      function (_ContractAPI_, _ContractInstance_, _ModelInstance_) {
        ContractInstance = _ContractInstance_;
        ModelInstance = _ModelInstance_;
        ContractAPI = _ContractAPI_;
      }]));

    beforeEach(function () {
      spyOn(ContractAPI, 'all');
    });

    it('inherits from ModelInstance', function () {
      expect(_.functions(ContractInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('all()', function () {
      beforeEach(function () {
        ContractInstance.all();
      });

      it('calls all of Contract API', function () {
        expect(ContractAPI.all).toHaveBeenCalled();
      });
    });
  });
});
