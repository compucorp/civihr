// Karma configuration
// Generated on Mon Sep 21 2015 08:25:08 GMT+0100 (BST)

module.exports = function (config) {
  var basePath = '../../../../';
  //var extPath = basePath + 'modules/civicrm/tools/extensions/civihr/contactsummary/';
  //var extPath = basePath + 'tools/extensions/civihr/contactsummary/';
  var extPath = 'tools/extensions/civihr/contactsummary/';

  config.set({
    // base path that will be used to resolve all patterns (eg. files, exclude)
    //basePath: '',
    basePath: basePath,

    plugins: [
      'karma-requirejs',
      'karma-jasmine',
      'karma-chrome-launcher'
    ],

    client: {
      requireJsShowNoTimestampsError: false
    },

    // frameworks to use
    // available frameworks: https://npmjs.org/browse/keyword/karma-adapter
    frameworks: ['jasmine', 'requirejs'],

    // list of files / patterns to load in the browser
    files: [
      extPath + 'js/test-main.js',
      extPath + 'js/contactsummary-main.js',
      {pattern: extPath + 'js/vendor/**/*.js', included: false},
      {pattern: extPath + 'js/vendor/**/*.map', included: false},
      {pattern: extPath + 'js/app.js', included: false},
      {pattern: extPath + 'js/controllers/**/*.js', included: false},
      {pattern: extPath + 'js/directives/**/*.js', included: false},
      {pattern: extPath + 'js/filters/**/*.js', included: false},
      {pattern: extPath + 'js/services/**/*.js', included: false},
      {pattern: extPath + 'js/mocks/**/*.js', included: false},
      {pattern: extPath + 'js/test/**/*.js', included: false},
      //{pattern: 'js/Common.js', included: false},
      {pattern: 'js/**/*.js', included: false},
      {pattern: 'packages/jquery/plugins/**/*.js', included: false},
      {pattern: 'packages/jquery/jquery-ui/**/*.js', included: false}
    ],

    // list of files to exclude
    exclude: [],

    // preprocess matching files before serving them to the browser
    // available preprocessors: https://npmjs.org/browse/keyword/karma-preprocessor
    preprocessors: {},

    // test results reporter to use
    // possible values: 'dots', 'progress'
    // available reporters: https://npmjs.org/browse/keyword/karma-reporter
    reporters: ['progress'],

    // web server port
    port: 9876,

    // enable / disable colors in the output (reporters and logs)
    colors: true,

    // level of logging
    // possible values: config.LOG_DISABLE || config.LOG_ERROR || config.LOG_WARN || config.LOG_INFO || config.LOG_DEBUG
    logLevel: config.LOG_INFO,

    // enable / disable watching file and executing tests whenever any file changes
    autoWatch: false,

    // start these browsers
    // available browser launchers: https://npmjs.org/browse/keyword/karma-launcher
    browsers: ['Chrome'],

    // Continuous Integration mode
    // if true, Karma captures browsers, runs the tests and exits
    singleRun: false
  });
};
