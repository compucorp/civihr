module.exports = function (config) {
    var civicrmPath = '../../../../../../';
    var civihrPath = 'tools/extensions/civihr/';

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
            civihrPath + 'org.civicrm.reqangular/node_modules/requirejs/require.js',
            civihrPath + 'org.civicrm.reqangular/node_modules/karma-requirejs/lib/adapter.js',

            // the requireJS config file that bootstraps the whole test suite
            civihrPath + 'org.civicrm.reqangular/src/tests/test-main.js',

            // load vendor libraries
            { pattern: civihrPath + 'org.civicrm.reqangular/src/common/vendor/*.min.js', included: false },

            // load modules
            { pattern: civihrPath + 'org.civicrm.reqangular/src/common/**/*.js', included: false },

            // load tests
            { pattern: civihrPath + 'org.civicrm.reqangular/src/tests/**/*_test.js', included: false }
        ],
        exclude: [
            civihrPath + 'org.civicrm.reqangular/src/common/angular-date/**/*.js'
        ]
    });
};
