var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];

Object.keys(window.__karma__.files).forEach(function(file) {
    if (TEST_REGEXP.test(file)) {
        allTestFiles.push(file);
    }
});

require.config({
    deps: allTestFiles,
    waitSeconds: 60,
    paths: {
        'contact-summary': '/base/tools/extensions/civihr/contactsummary/js/src/contact-summary'
    },
    callback: window.__karma__.start
});
