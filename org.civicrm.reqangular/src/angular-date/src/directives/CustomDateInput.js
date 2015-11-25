module.exports = function CustomDateInput($filter) {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModelController) {

            function convert(data){
                var output = $filter('CustomDate')(data);

                output = (output == 'Unspecified')? '' : output;

                return output;
            }

            ngModelController.$formatters.push(convert);
        }
    };
};
