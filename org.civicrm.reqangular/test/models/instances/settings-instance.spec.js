/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/settings-instance'
], function (_) {
  'use strict';

  describe('SettingsInstance', function () {
    var SettingsInstance, ModelInstance;

    beforeEach(module('common.models.instances'));

    beforeEach(inject(['SettingsInstance', 'ModelInstance',
      function (_SettingsInstance_, _ModelInstance_) {
        SettingsInstance = _SettingsInstance_;
        ModelInstance = _ModelInstance_;
      }
    ]));

    it('inherits from ModelInstance', function () {
      expect(_.functions(SettingsInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });
  });
});
