/* eslint-env amd */
/* globals location */

(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    'use strict';

    angular.module('contactsummary.constants', []).constant('settings', {
      contactId: decodeURIComponent((new RegExp('[?|&]cid=([^&;]+?)(&|#|;|$)').exec(location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null,
      debug: +CRM.debug,
      baseUrl: CRM.vars.contactsummary.baseURL + '/js/src/contact-summary/',
      CRM: {
        options: CRM.FieldOptions || {}
      }
    });
  });
}(CRM));
