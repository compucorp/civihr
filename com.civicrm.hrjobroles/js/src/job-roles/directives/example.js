define([
    'job-roles/directives/directives'
], function (directives) {
    'use strict';

    directives.directive('example',['$rootScope','$log',function ($rootScope, $log) {
        $log.debug('Directive: example');

        return {
            link: function ($scope, el, attrs) {

            }
        }
    }]);
});
