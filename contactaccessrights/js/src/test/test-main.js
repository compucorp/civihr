var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var mocksPath = CRM.vars.contactAccessRights.baseURL + '/js/test/mocks';
var srcPath = CRM.vars.contactAccessRights.baseURL + '/js/src/access-rights';

Object.keys(window.__karma__.files).forEach(function(file) {
    if (TEST_REGEXP.test(file)) {
        allTestFiles.push(file);
    }
});

require.config({
    deps: allTestFiles,
    waitSeconds: 60,
    paths: {
        'access-rights': srcPath,
        'mocks': mocksPath
    },
    callback: function () {
        window.__karma__.start();
    }
});
