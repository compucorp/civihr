var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var extPath = '/base/tools/extensions/civihr/org.civicrm.reqangular';
var mocksPath = extPath + '/test/mocks';
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
        'common/angularUiRouter': {
            deps: ['common/angular']
        },
        'common/angularUiRouter': {
            deps: ['common/angular']
        },
        'common/ui-select': {
            deps: [
              'common/angular',
              'common/text-angular-sanitize'
            ]
        },
        'common/angular-file-upload': {
            deps: ['common/angular']
        },
    },
    paths: {
        'common': srcPath,
        'common/mocks': mocksPath,
        'common/angular': srcPath + '/vendor/angular/angular.min',
        'common/moment': srcPath + '/vendor/moment.min',
        'common/angularRoute': srcPath + '/vendor/angular/angular-route.min',
        'common/angularUiRouter': srcPath + '/vendor/angular/angular-ui-router.min',
        'common/angularMocks': srcPath + '/vendor/angular/angular-mocks',
        'common/angularBootstrap': srcPath + '/vendor/angular/ui-bootstrap',
        'common/lodash': srcPath + '/vendor/lodash.min',
        'common/ui-select': srcPath + '/vendor/angular/select',
        'common/vendor/perfect-scrollbar': srcPath + '/vendor/perfect-scrollbar.min',
        'common/text-angular-sanitize': srcPath + '/vendor/angular/textAngular-sanitize.min',
        'common/angular-file-upload': srcPath + '/vendor/angular/angular-file-upload'
    },
    callback: function () {
        // Simple hack to provide value to CRM.vars.reqangular.baseURL
        CRM.vars = { reqangular: { baseURL: extPath } };

        window.__karma__.start();
    }
});
