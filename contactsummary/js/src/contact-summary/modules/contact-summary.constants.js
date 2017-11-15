/* eslint-env amd */
/* globals location */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('contactsummary.constants', []).constant('settings', {
    classNamePrefix: 'contactSummary-',
    contactId: decodeURIComponent((new RegExp('[?|&]cid=([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null,
    debug: true,
    pathApp: '',
    pathRest: CRM.url('civicrm/ajax/rest'),
    pathBaseUrl: CRM.vars.contactsummary.baseURL + '/',
    pathTpl: 'views/'
  });
});
