/* eslint-env amd */
/* global CustomEvent */

(function () {
  'use strict';

  var extPath = CRM.vars.contactAccessRights.baseURL + '/js/src/access-rights';
  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'access-rights': extPath
    }
  });

  require([
    'access-rights/modules/access-rights.module'
  ], function (angular) {
    document.dispatchEvent(typeof window.CustomEvent === 'function' ? new CustomEvent('accessRightsReady') : (function () {
      var e = document.createEvent('Event');
      e.initEvent('accessRightsReady', true, true);
      return e;
    })());
  });
})(require);
