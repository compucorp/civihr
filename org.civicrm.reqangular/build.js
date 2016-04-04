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
        'common/angularUiRouter': 'common/vendor/angular/angular-ui-router.min',
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
    },
    include: [
        'common/bundles/vendors',
        'common/bundles/apis',
        'common/bundles/services',
        'common/bundles/directives',
        'common/modules/templates',
        'common/bundles/angular-date',
        'common/bundles/routers'
    ]
})
