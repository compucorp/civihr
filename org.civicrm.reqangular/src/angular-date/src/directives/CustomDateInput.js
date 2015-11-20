module.exports = function CustomDateInput($filter) {
    return {
        require: 'ngModel',
        link: function(scope, element, attrs, ngModelController) {

            function convert(data){
                return $filter('CustomDate')(data);
            }

            ngModelController.$formatters.push(convert);
        }
    };
};
