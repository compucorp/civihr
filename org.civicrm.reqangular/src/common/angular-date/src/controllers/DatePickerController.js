/**
 * @extends DatepickerController
 */
function DatePickerController($scope, $controller, DateFactory, $log){
    $scope.implements = 'DatepickerController';

    var me = this,
        ngModelCtrl = { $setViewValue: angular.noop };

    angular.extend(this, $controller('DatepickerController', {
        $scope: $scope,
        $attrs: {}
    }));


    $scope.isActive = function(dateObject) {
        if (me.compare(dateObject.date, me.activeDate) === 0) {
            $scope.activeDateId = dateObject.uid;
            return true;
        }
        return false;
    };

    me.parseDate = function(date){
        var formatted = DateFactory.createDate(date).format('DD/MM/YYYY');

        var newDate = DateFactory.moment(formatted, 'DD/MM/YYYY');

        return newDate.toDate();
    };

    /* Overriding Methods */
    me.render = function() {
        if ( ngModelCtrl.$modelValue ) {

            var date = me.parseDate(ngModelCtrl.$modelValue),
                isValid = !isNaN(date);

            if ( isValid ) {
                me.activeDate = date;
            } else {
                $log.error('Date is Invalid.');
            }
            ngModelCtrl.$setValidity('date', isValid);

            console.log(ngModelCtrl.$modelValue, 'results in', date);
        }

        me.refreshView();
    };


    me.refreshView = function() {
        if (me.element) {
            me._refreshView();

            var date = ngModelCtrl.$modelValue ? me.parseDate(ngModelCtrl.$modelValue) : null;

            ngModelCtrl.$setValidity('date-disabled', !date || (me.element && !me.isDisabled(date)));
        }
    };

    this.init = function(ngModelCtrl_) {
        ngModelCtrl = ngModelCtrl_;

        ngModelCtrl.$render = function() {
            me.render();
        };
    };
}

module.exports = DatePickerController;