var TEST_REGEXP = /\.spec\.js$/i;
var allTestFiles = [];
var srcPath = '/base/tools/extensions/civihr/contactaccessrights/js/src/access-rights';

Object.keys(window.__karma__.files).forEach(function (file) {
  if (TEST_REGEXP.test(file)) {
    allTestFiles.push(file);
  }
});

require.config({
  deps: allTestFiles,
  waitSeconds: 60,
  paths: {
    'access-rights': srcPath
  },
  callback: function () {
    window.__karma__.start();
  }
});
