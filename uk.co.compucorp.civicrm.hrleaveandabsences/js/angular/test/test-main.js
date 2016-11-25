var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var mocksPath = CRM.vars.leaveAbsences.baseURL + '/js/angular/test/mocks';
var srcPath = CRM.vars.leaveAbsences.baseURL + '/js/angular/src/leave-absences';

Object.keys(window.__karma__.files).forEach(function(file) {
  if (TEST_REGEXP.test(file)) {
    allTestFiles.push(file);
  }
});

require.config({
  deps: allTestFiles,
  waitSeconds: 60,
  paths: {
    'leave-absences': srcPath,
    'leave-absences/vendor/ui-router': srcPath + '/vendor/angular-ui-router.min',
    'mocks': mocksPath
  },
  callback: function () {
    window.__karma__.start();
  }
});
