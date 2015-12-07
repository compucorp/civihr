var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var extPath = '/base/tools/extensions/civihr/uk.co.compucorp.civicrm.appraisals';
var srcPath = extPath + '/js/src/appraisals';

Object.keys(window.__karma__.files).forEach(function(file) {
    if (TEST_REGEXP.test(file)) {
        allTestFiles.push(file);
    }
});

require.config({
    deps: allTestFiles,
    waitSeconds: 60,
    paths: {
        'appraisals': srcPath,
        'appraisals/vendor/ui-router': srcPath + '/vendor/angular-ui-router.min'
    },
    callback: function () {
        // Simple hack to provide value to CRM.vars.appraisals.baseURL
        CRM.vars = { appraisals: { baseURL: extPath } };

        window.__karma__.start();
    }
});
