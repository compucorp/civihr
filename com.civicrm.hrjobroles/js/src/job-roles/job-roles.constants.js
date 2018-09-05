/* eslint-env amd */
/* globals location */

(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    'use strict';

    angular.module('hrjobroles.constants', [])
      .constant('settings', {
        classNamePrefix: 'hrjobroles-',
        contactId: decodeURIComponent((new RegExp('[?|&]cid=([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null,
        debug: +CRM.debug,
        baseUrl: CRM.vars.hrjobroles.baseURL + '/js/src/job-roles/'
      });
  });
}(CRM));
