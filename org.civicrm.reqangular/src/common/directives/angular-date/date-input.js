define([
    'common/modules/angular-date',
    'common/filters/angular-date/format-date'
], function (module) {
    'use strict';
    module.directive('dateInput', ['$filter', function ($filter) {
        return {
            require: 'ngModel',
            link: function(scope, element, attrs, ngModelController) {

                function convert(data){
                    var output = $filter('formatDate')(data);

                    output = (output == 'Unspecified')? '' : output;

                    return output;
                }

                ngModelController.$formatters.push(convert);

                ngModelController.$parsers = [];
            }
        };
    }]);
});
