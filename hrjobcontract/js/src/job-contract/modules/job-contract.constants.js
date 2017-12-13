/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('job-contract.constants', []).constant('settings', {
    classNamePrefix: 'hrjc-',
    contactId: CRM.jobContractTabApp.contactId,
    debug: +CRM.debug,
    pathApp: CRM.jobContractTabApp.path,
    pathFile: CRM.url('civicrm/hrjobcontract/file/'),
    pathReport: CRM.url('civicrm/report/hrjobcontract/summary'),
    pathRest: CRM.url('civicrm/ajax/rest'),
    pathTpl: CRM.jobContractTabApp.path + 'views/',
    CRM: {
      options: CRM.FieldOptions || {},
      defaultCurrency: CRM.jobContractTabApp.defaultCurrency,
      apiTsFmt: 'YYYY-MM-DD HH:mm:ss',
      fields: CRM.jobContractTabApp.fields,
      maxFileSize: CRM.jobContractTabApp.maxFileSize
    }
  });
});
