module.exports = function (config) {
  var civicrmPath = '../../../../';
  var civihrPath = 'tools/extensions/civihr/';
  var extPath = civihrPath + 'org.civicrm.reqangular/';

  config.set({
    basePath: civicrmPath,
    browsers: ['Chrome'],
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
      extPath + 'node_modules/requirejs/require.js',
      extPath + 'node_modules/karma-requirejs/lib/adapter.js',

            // load test helpers
            { pattern: extPath + 'test/helpers/**/*.helper.js', included: true },

            // load vendor libraries
            { pattern: extPath + 'src/common/vendor/*.min.js', included: false },

            // load modules
            { pattern: extPath + 'src/common/**/*.js', included: false },

            // the mocked components files
            { pattern: extPath + 'test/mocks/**/*.js', included: false },

            // load tests
            { pattern: extPath + 'test/**/*.spec.js', included: false },

            // the requireJS config file that bootstraps the whole test suite
      extPath + 'test/test-main.js'
    ],
    exclude: [
      extPath + 'src/common/angular-date/**/*.js'
    ]
  });
};
