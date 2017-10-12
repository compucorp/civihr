/* globals CRM */

var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var mocksPath = CRM.vars.contactsummary.baseURL + '/js/test/mocks';
var srcPath = CRM.vars.contactsummary.baseURL + '/js/src/contact-summary';

Object.keys(window.__karma__.files).forEach(function (file) {
  if (TEST_REGEXP.test(file)) {
    allTestFiles.push(file);
  }
});

require.config({
  deps: allTestFiles,
  waitSeconds: 60,
  paths: {
    'contact-summary': srcPath,
    'mocks': mocksPath,
    'leave-absences': CRM.vars.contactsummary.baseURL + '/../uk.co.compucorp.civicrm.hrleaveandabsences/js/angular/src/leave-absences'
  },
  callback: function () {
    window.__karma__.start();
  }
});
