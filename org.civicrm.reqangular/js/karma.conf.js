var argv = require('yargs').argv;

module.exports = function (config) {
  var civicrmPath = '../../../../../';
  var civihrPath = 'tools/extensions/civihr/';
  var extPath = civihrPath + 'org.civicrm.reqangular/';

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

      // manual loading of requirejs as to avoid interference with the global dependencies above
      civihrPath + 'uk.co.compucorp.civicrm.hrcore/node_modules/requirejs/require.js',
      civihrPath + 'uk.co.compucorp.civicrm.hrcore/node_modules/karma-requirejs/lib/adapter.js',

      // load test helpers
      { pattern: extPath + 'js/test/helpers/**/*.helper.js', included: true },

      // load vendor libraries
      { pattern: extPath + 'js/src/common/vendor/*.min.js', included: false },

      // load modules
      { pattern: extPath + 'js/src/common/**/*.js', included: false },

      // the mocked components files
      { pattern: extPath + 'js/test/mocks/**/*.js', included: false },

      // load tests
      { pattern: extPath + 'js/test/**/*.spec.js', included: false },

      // the requireJS config file that bootstraps the whole test suite
      extPath + 'js/test/test-main.js'
    ],
    exclude: [
      extPath + 'js/src/common/angular-date/**/*.js'
    ],
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
    reporters: argv.reporters ? argv.reporters.split(',') : ['spec'],
    specReporter: {
      suppressSkipped: true
    },
    junitReporter: {
      outputDir: extPath + 'test-reports',
      useBrowserName: false,
      outputFile: 'reqangular.xml'
    }
  });
};
