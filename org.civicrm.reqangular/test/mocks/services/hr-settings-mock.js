/* eslint-env amd */

define([
  'common/mocks/module'
], function (mocks) {
  'use strict';

  mocks.factory('HR_settingsMock', function () {
    return {
      DATE_FORMAT: 'dd/MM/yyyy'
    };
  });
});
