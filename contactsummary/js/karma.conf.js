module.exports = function (config) {
    var civicrmPath = '../../../../../';
    var civihrPath = 'tools/extensions/civihr/';

    config.set({
        basePath: civicrmPath,
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
            civihrPath + 'contactsummary/node_modules/requirejs/require.js',
            civihrPath + 'contactsummary/node_modules/karma-requirejs/lib/adapter.js',

            // all the common/ dependencies
            civihrPath + 'org.civicrm.reqangular/dist/reqangular.min.js',

            // the application modules
            { pattern: civihrPath + 'contactsummary/js/src/contact-summary/**/*.js', included: false },

            // the test files
            { pattern: civihrPath + 'contactsummary/js/test/**/*_test.js', included: false },

            // the requireJS config file that bootstraps the whole test suite
            civihrPath + 'contactsummary/js/test/test-main.js'
        ],
        browsers: ['Chrome'],
    });
};
