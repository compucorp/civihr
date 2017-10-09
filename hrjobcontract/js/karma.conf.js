module.exports = function (config) {
  var civicrmPath = '../../../../../';
  var civihrPath = 'tools/extensions/civihr/';
  var extPath = civihrPath + 'hrjobcontract/';

  config.set({
    basePath: civicrmPath,
    browsers: ['ChromeHeadless'],
    frameworks: ['jasmine'],
    files: [
      // the global dependencies
      'bower_components/jquery/dist/jquery.min.js',
      'bower_components/jquery-ui/jquery-ui.js',
      'bower_components/lodash-compat/lodash.min.js',
      'bower_components/select2/select2.min.js',
      'bower_components/jquery-validation/dist/jquery.validate.min.js',
      'packages/jquery/plugins/jquery.mousewheel.min.js',
      'packages/jquery/plugins/jquery.blockUI.js',
      'js/Common.js',
      'js/crm.ajax.js',

      // Global variables that need to be accessible in the test environment
      extPath + 'js/test/globals.js',

      // manual loading of requirejs as to avoid interference with the global dependencies above
      extPath + 'node_modules/requirejs/require.js',
      extPath + 'node_modules/karma-requirejs/lib/adapter.js',

      // all the common/ dependencies
      civihrPath + 'org.civicrm.reqangular/dist/reqangular.min.js',

      // all the common/ mocked dependencies
      civihrPath + 'org.civicrm.reqangular/dist/reqangular.mocks.min.js',

      // the application modules
      { pattern: extPath + 'js/src/job-contract/**/*.js', included: false },

      // the mocked components files
      { pattern: extPath + 'js/test/mocks/**/*.js', included: false },

      // the test files
      { pattern: extPath + 'js/test/**/*_test.js', included: false },

      // angular templates
      extPath + 'views/**/*.html',

      // the requireJS config file that bootstraps the whole test suite
      extPath + 'js/test/test-main.js'
    ],
    exclude: [
      extPath + 'js/src/job-contract.js'
    ],
    // Used to transform angular templates in JS strings
    preprocessors: (function (obj) {
      obj[extPath + 'views/**/*.html'] = ['ng-html2js'];
      return obj;
    })({}),
    ngHtml2JsPreprocessor: {
      prependPrefix: '/base/',
      moduleName: 'job-contract.templates'
    },
    customLaunchers: {
      ChromeHeadless: {
        base: 'Chrome',
        flags: [
          '--headless',
          '--disable-gpu',
          // Without a remote debugging port, Google Chrome exits immediately.
          '--remote-debugging-port=9222'
        ]
      }
    },
    junitReporter: {
      outputDir: extPath + 'test-reports',
      useBrowserName: false,
      outputFile: 'hrjobcontract.xml'
    }
  });
};
