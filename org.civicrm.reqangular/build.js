({
    baseUrl : 'src',
    out: 'dist/reqangular.min.js',
    uglify: {
        no_mangle: true,
        max_line_length: 1000
    },
    paths: {
        'common/angular': 'common/vendor/angular/angular.min',
        'common/angularAnimate': 'common/vendor/angular/angular-animate.min',
        'common/angularBootstrap': 'common/vendor/angular/ui-bootstrap-tpls',
        'common/angularFileUpload': 'common/vendor/angular/angular-file-upload',
        'common/angularMocks': 'common/vendor/angular/angular-mocks',
        'common/angularResource': 'common/vendor/angular/angular-resource.min',
        'common/angularRoute': 'common/vendor/angular/angular-route.min',
        'common/angular-date': 'common/angular-date/dist/angular-date',
        'common/require': 'common/vendor/require.min',
        'common/d3': 'common/vendor/d3.min',
        'common/lodash': 'common/vendor/lodash.min',
        'common/moment': 'common/vendor/moment.min'
    },
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
    include: [
        'common/bundles/vendors',
        'common/bundles/apis',
        'common/bundles/settings',
        'common/bundles/routes',
        'common/modules/templates',
        'common/directives/loading',
        'common/services/dialog',
        'common/bundles/angularDate'
    ]
})
