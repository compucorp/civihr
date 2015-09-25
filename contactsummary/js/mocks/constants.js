define(['mocks/module'], function (module) {
  'use strict';

  var settingsMock = jasmine.createSpyObj('settings', ['pathBaseUrl', 'pathTpl']);

  module.constant('settingsMock', settingsMock);
});