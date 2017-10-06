/* eslint-env amd */

(function (CRM, require) {
  var extPath = CRM.vars.hrjobroles.baseURL + '/js/src/job-roles';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'job-roles': extPath,
      'job-roles/vendor/angular-editable': extPath + '/vendor/angular/xeditable.min',
      'job-roles/vendor/angular-filter': extPath + '/vendor/angular/angular-filter.min'
    }
  });

  require(['job-roles/modules/job-roles.module'], function (app) {
    'use strict';

    document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('hrjobrolesReady') : (function () {
      var e = document.createEvent('Event');
      e.initEvent('hrjobrolesReady', true, true);
      return e;
    })());
  });
})(CRM, require);
