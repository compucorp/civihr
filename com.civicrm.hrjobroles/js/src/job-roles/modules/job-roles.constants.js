/* eslint-env amd */
/* globals location */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('hrjobroles.constants', [])
    .constant('settings', {
      classNamePrefix: 'hrjobroles-',
      contactId: decodeURIComponent((new RegExp('[?|&]cid=([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null,
      debug: true,
      pathApp: CRM.vars.hrjobroles.path,
      pathRest: CRM.url('civicrm/ajax/rest'),
      pathBaseUrl: CRM.vars.hrjobroles.baseURL + '/',
      pathTpl: 'views/',
      pathIncludeTpl: 'views/include/'
    });
});
