/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('access-rights.constants', [])
    .constant('settings', {
      debug: +CRM.debug,
      baseUrl: CRM.vars.contactAccessRights.baseURL + '/js/src/access-rights/'
    });
});
