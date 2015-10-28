var isTest = true;
var extJs = '/base/tools/extensions/civihr/contactsummary/js';
var specFiles = [];

// Get a list of all the test files to include
for (var file in window.__karma__.files) {
  if (window.__karma__.files.hasOwnProperty(file)) {
    if (/\/base\/tools\/extensions\/civihr\/contactsummary\/js\/.*_test\.js$/.test(file)) {
      //specFiles.push(file);
      specFiles.push(file.replace(/^\/base\//, 'http://localhost:9876/base/')); // fixes timestamp errors
    }
  }
}

//console.log(window.__karma__.files);
//console.log(specFiles);

require.config({
  // Karma serves files under /base, which is the basePath from the config file
  //baseUrl: '/base/js',
  baseUrl: 'http://localhost:9876' + extJs, // fixes timestamp errors

  waitSeconds: 60, // fixes frequent timeouts

  // dynamically load all test files
  deps: specFiles,

  // we have to kickoff jasmine, as it is asynchronous
  callback: window.__karma__.start,

  // todo: can be refactored
  paths: {
    //angular: 'vendor/angular/angular.min',
    //angularBootstrap: 'vendor/angular/ui-bootstrap-tpls',
    //angularResource: 'vendor/angular/angular-resource.min',
    //angularRoute: 'vendor/angular/angular-route.min',
    angularMocks: 'vendor/angular/angular-mocks',
    jQuery: 'vendor/jquery.min',
    jQuerySelect2: 'http://localhost:9876/base/packages/jquery/plugins/select2/select2',
    jQueryUI: 'http://localhost:9876/base/packages/jquery/jquery-ui/jquery-ui',
    lodash: 'vendor/lodash.min',
    CiviCommon: 'http://localhost:9876/base/js/Common'
  },

  shim: {
    //angular: {
    //  exports: 'angular'
    //},
    //angularBootstrap: {
    //  deps: ['angular']
    //},
    //angularResource: {
    //  deps: ['angular']
    //},
    //angularRoute: {
    //  deps: ['angular']
    //},
    angularMocks: {
      deps: ['angular']
    },
    jQuery: {
      exports: 'jQuery'
    },
    CiviCommon: {
      exports: 'CiviCommon',
      deps: ['jQuery', 'jQuerySelect2', 'jQueryUI']
    },
    jQuerySelect2: {
        deps: ['jQuery']
    },
    jQueryUI: {
        deps: ['jQuery']
    }
  }
});
