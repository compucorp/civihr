define(['directives/directives'], function(directives){
    directives.directive('customDateInput', function($filter) {
        return {
            require: 'ngModel',
            link: function(scope, element, attrs, ngModelController) {

                function convert(data){
                    return $filter('customDate')(data);
                }

                ngModelController.$formatters.push(convert);
            }
        }
    });
});