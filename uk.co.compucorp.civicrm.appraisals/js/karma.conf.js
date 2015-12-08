module.exports = function (config) {
    var civicrmPath = '../../../../../';
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
            civihrPath + 'uk.co.compucorp.civicrm.appraisals/node_modules/requirejs/require.js',
            civihrPath + 'uk.co.compucorp.civicrm.appraisals/node_modules/karma-requirejs/lib/adapter.js',

            // all the common/ dependencies
            civihrPath + 'org.civicrm.reqangular/dist/reqangular.min.js',

            // the application modules
            { pattern: civihrPath + 'uk.co.compucorp.civicrm.appraisals/js/src/appraisals/**/*.js', included: false },

            // the test files
            { pattern: civihrPath + 'uk.co.compucorp.civicrm.appraisals/js/test/**/*_test.js', included: false },

            // angular templates
            civihrPath + 'uk.co.compucorp.civicrm.appraisals/views/**/*.html',

            // the requireJS config file that bootstraps the whole test suite
            civihrPath + 'uk.co.compucorp.civicrm.appraisals/js/test/test-main.js'
        ],
        exclude: [
            civihrPath + 'uk.co.compucorp.civicrm.appraisals/js/src/appraisals.js'
        ],
        // Used to transform angular templates in JS strings
        preprocessors: (function (obj) {
            obj[civihrPath + 'uk.co.compucorp.civicrm.appraisals/views/**/*.html'] = ['ng-html2js'];
            return obj;
        })({}),
        ngHtml2JsPreprocessor: {
            prependPrefix: '/base/',
            moduleName: 'appraisals.templates'
        }
    });
};
