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
            'packages/jquery/jquery-1.11.1.js',
            'packages/jquery/jquery-ui/jquery-ui.js',
            'packages/backbone/lodash.compat.js',
            'packages/jquery/plugins/jquery.mousewheel.js',
            'packages/jquery/plugins/select2/select2.js',
            'packages/jquery/plugins/jquery.blockUI.js',
            'js/Common.js',

            // manual loading of requirejs as to avoid interference with the global dependencies above
            extPath + 'node_modules/requirejs/require.js',
            extPath + 'node_modules/karma-requirejs/lib/adapter.js',

            // load vendor libraries
            { pattern: extPath + 'src/common/vendor/*.min.js', included: false },

            // load modules
            { pattern: extPath + 'src/common/**/*.js', included: false },

            // the mocked components files
            { pattern: extPath + 'test/mocks/**/*.js', included: false },

            // load tests
            { pattern: extPath + 'test/**/*_test.js', included: false },

            // the requireJS config file that bootstraps the whole test suite
            extPath + 'test/test-main.js'
        ],
        exclude: [
            extPath + 'src/common/angular-date/**/*.js'
        ]
    });
};
