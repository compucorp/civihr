/* eslint-env amd, jasmine */

define([
  'mocks/module.mock'
], function (mocks) {
  'use strict';

  var settingsMock = {
    pathBaseUrl: '',
    pathTpl: '',
    contactId: 123
  };

  mocks.constant('settingsMock', settingsMock);
});
