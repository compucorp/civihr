/* eslint-env amd */

(function (CRM) {
  define([
    'common/angular'
  ], function (angular) {
    'use strict';

    angular.module('job-contract.constants', []).constant('settings', {
      classNamePrefix: 'hrjc-',
      contactId: CRM.vars.hrjobcontract.contactId,
      debug: +CRM.debug,
      baseUrl: CRM.vars.hrjobcontract.baseURL + 'js/src/job-contract/',
      pathFile: CRM.url('civicrm/hrjobcontract/file/'),
      pathReport: CRM.url('civicrm/report/hrjobcontract/summary'),
      pathRest: CRM.url('civicrm/ajax/rest'),
      CRM: {
        options: CRM.FieldOptions || {},
        defaultCurrency: CRM.vars.hrjobcontract.defaultCurrency,
        apiTsFmt: 'YYYY-MM-DD HH:mm:ss',
        fields: CRM.vars.hrjobcontract.fields,
        maxFileSize: CRM.vars.hrjobcontract.maxFileSize
      }
    });
  });
}(CRM));
