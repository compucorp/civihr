({
  baseUrl: 'src',
  out: 'dist/reqangular.min.js',
  uglify: {
    no_mangle: true,
    max_line_length: 1000
  },
  paths: {
    'common/angular': 'common/vendor/angular/angular.min',
    'common/angularAnimate': 'common/vendor/angular/angular-animate.min',
    'common/angularBootstrap': 'common/vendor/angular/ui-bootstrap',
    'common/angular-file-upload': 'common/vendor/angular/angular-file-upload',
    'common/angularMocks': 'common/vendor/angular/angular-mocks',
    'common/angularResource': 'common/vendor/angular/angular-resource.min',
    'common/angularRoute': 'common/vendor/angular/angular-route.min',
    'common/angularUiRouter': 'common/vendor/angular/angular-ui-router.min',
    'common/angularXeditable': 'common/vendor/angular/xeditable',
    'common/rangy-core': 'common/vendor/angular/rangy-core',
    'common/rangy-selectionsaverestore': 'common/vendor/angular/rangy-selectionsaverestore',
    'common/text-angular-sanitize': 'common/vendor/angular/textAngular-sanitize.min',
    'common/text-angular-setup': 'common/vendor/angular/textAngularSetup',
    'common/text-angular': 'common/vendor/angular/textAngular',
    'common/ui-select': 'common/vendor/angular/select',
    'common/require': 'common/vendor/require.min',
    'common/d3': 'common/vendor/d3.min',
    'common/lodash': 'common/vendor/lodash.min',
    'common/moment': 'common/vendor/moment.min',
    'common/mocks': '../test/mocks',
    'common/vendor/perfect-scrollbar': 'common/vendor/perfect-scrollbar.min'
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
    'common/angularXeditable': {
      deps: ['common/angular']
    },
    'common/text-angular': {
      deps: [
        'common/rangy-core',
        'common/rangy-selectionsaverestore',
        'common/text-angular-sanitize',
        'common/text-angular-setup'
      ]
    },
    'common/ui-select': {
      deps: ['common/angular']
    }
  },
  include: [
    'common/bundles/vendors',
    'common/bundles/apis',
    'common/bundles/services',
    'common/bundles/directives',
    'common/bundles/angular-date',
    'common/bundles/routers',
    'common/bundles/models',
    'common/modules/dialog',
    'common/modules/templates',
    'common/modules/xeditable-civi'
  ]
})
