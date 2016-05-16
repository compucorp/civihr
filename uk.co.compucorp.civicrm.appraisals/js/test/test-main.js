var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var mocksPath = CRM.vars.appraisals.baseURL + '/js/test/mocks';
var srcPath = CRM.vars.appraisals.baseURL + '/js/src/appraisals';

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
        'appraisals/vendor/ui-router': srcPath + '/vendor/angular-ui-router.min',
        'mocks': mocksPath
    },
    callback: function () {
        window.__karma__.start();
    }
});
