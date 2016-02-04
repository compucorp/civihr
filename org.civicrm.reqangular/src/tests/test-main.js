var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var extPath = '/base/tools/extensions/civihr/org.civicrm.reqangular';
var mocksPath = extPath + '/src/tests/mocks';
var srcPath = extPath + '/src/common';

Object.keys(window.__karma__.files).forEach(function(file) {
    if (TEST_REGEXP.test(file)) {
        allTestFiles.push(file);
    }
});

require.config({
    deps: allTestFiles,
    waitSeconds: 60,
    shim: {
        'common/angular': {
            exports: 'angular'
        },
        'common/angularAnimate': {
            deps: ['common/angular']
        },
        'common/angular-date': {
            deps: ['common/angular']
        },
        'common/angularBootstrap': {
            deps: ['common/angular']
        },
        'common/angularMocks': {
            deps: ['common/angular']
        },
        'common/angularResource': {
            deps: ['common/angular']
        },
        'common/angularRoute': {
            deps: ['common/angular']
        },
    },
    paths: {
        'common': srcPath,
        'mocks': mocksPath,
        'common/angular': srcPath + '/vendor/angular/angular.min',
        'common/moment': srcPath + '/vendor/moment.min',
        'common/angularMocks': srcPath + '/vendor/angular/angular-mocks'
    },
    callback: function () {
        // Simple hack to provide value to CRM.vars.reqangular.baseURL
        CRM.vars = { reqangular: { baseURL: extPath } };

        window.__karma__.start();
    }
});
