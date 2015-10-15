define(['mocks/module'], function (module) {
  'use strict';

  var settingsMock = {
    pathBaseUrl: '',
    pathTpl: '',
    contactId: 123
  };

  module.constant('settingsMock', settingsMock);
});