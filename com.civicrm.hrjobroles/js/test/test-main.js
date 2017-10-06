var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var mocksPath = CRM.vars.hrjobroles.baseURL + '/js/test/mocks';
var srcPath = CRM.vars.hrjobroles.baseURL + '/js/src/job-roles';

Object.keys(window.__karma__.files).forEach(function (file) {
  if (TEST_REGEXP.test(file)) {
    allTestFiles.push(file);
  }
});

require.config({
  deps: allTestFiles,
  waitSeconds: 60,
  paths: {
    'job-roles': srcPath,
    'job-roles/vendor/angular-editable': srcPath + '/vendor/angular/xeditable.min',
    'job-roles/vendor/angular-filter': srcPath + '/vendor/angular/angular-filter.min',
    'mocks': mocksPath
  },
  callback: function () {
    window.__karma__.start();
  }
});
