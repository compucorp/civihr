var specFiles = [];

// Get a list of all the test files to include
for (var file in window.__karma__.files) {
  if (window.__karma__.files.hasOwnProperty(file)) {
    if (/\/base\/js\/.*_test\.js$/.test(file)) {
      //specFiles.push(file);
      specFiles.push(file.replace(/^\/base\//, 'http://localhost:9876/base/')); // fixes timestamp errors
    }
  }
}

require.config({
  // Karma serves files under /base, which is the basePath from the config file
  //baseUrl: '/base/js',
  baseUrl: 'http://localhost:9876/base/js', // fixes timestamp errors

  waitSeconds: 20, // fixes frequent timeouts

  // dynamically load all test files
  deps: specFiles,

  // we have to kickoff jasmine, as it is asynchronous
  callback: window.__karma__.start,

  // todo: can be refactored
  paths: {
    angular: 'vendor/angular/angular.min',
    angularBootstrap: 'vendor/angular/ui-bootstrap-tpls',
    angularResource: 'vendor/angular/angular-resource.min',
    angularRoute: 'vendor/angular/angular-route.min',
    angularMocks: 'vendor/angular/angular-mocks',
    jQuery: 'vendor/jquery.min',
    lodash: 'vendor/lodash.min'
  },

  shim: {
    angular: {
      exports: 'angular'
    },
    angularBootstrap: {
      deps: ['angular']
    },
    angularResource: {
      deps: ['angular']
    },
    angularRoute: {
      deps: ['angular']
    },
    angularMocks: {
      deps: ['angular']
    }
  }
});
