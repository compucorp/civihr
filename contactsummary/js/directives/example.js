define(['directives/directives'], function(directives){
    directives.directive('example',['$rootScope','$log',function(){
        $log.debug('Directive: example');

        return {
            link: function ($scope, el, attrs) {

            }
        }
    }]);
});