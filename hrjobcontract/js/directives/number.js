define(['directives/directives'], function(directives){
    directives.directive('hrjcNumber',['$log',function($log){
        $log.debug('Directive: hrjcNumber');

        return {
            require: 'ngModel',
            link: function ($scope, el, attrs, modelCtrl) {
                var toFixedVal = 2,
                    notToFixed = attrs.hrjcNumberFloat || false;

                if (attrs.hrjcNumber && typeof +attrs.hrjcNumber === 'number') {
                    toFixedVal = attrs.hrjcNumber;
                }

                el.bind('blur', function () {
                    var viewVal = parseFloat(modelCtrl.$viewValue) || 0;

                    modelCtrl.$setViewValue(!notToFixed ? viewVal.toFixed(toFixedVal) : Math.round(viewVal * 100) / 100);
                    modelCtrl.$render();
                });
            }
        }
    }]);
});