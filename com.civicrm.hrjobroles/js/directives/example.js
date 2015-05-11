define(['directives/directives'], function(directives){
    directives.directive('example',['$rootScope','$log',function($rootScope, $log){
        $log.debug('Directive: example');

        return {
            link: function ($scope, el, attrs) {

            }
        }
    }]);
});