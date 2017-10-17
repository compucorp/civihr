/* eslint-env amd, jasmine */

define([
  'mocks/module'
], function (mocks) {
  'use strict';

  var settingsMock = {
    pathBaseUrl: '',
    pathTpl: '',
    contactId: 123
  };

  mocks.constant('settingsMock', settingsMock);
});
