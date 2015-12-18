function DatepickerPopupDirectiveDecorator($delegate) {
    var original_link = $delegate[0].link;


    // FIXME noassign error is caused by wrong binding - change it here
    // FIXME as for now i have no clue how to solve it
    //$delegate[0].scope.isOpen = '&';

    /**
     * Implements original link function
     * @returns Function
     */
    $delegate[0].compile = function(){
        return function(scope, element, attrs, ngModel){

            // TODO fetch form civicrm settings
            /**
             * @override
             * @type {string}
             */
            attrs.datepickerPopup = 'dd/MM/yyyy';

            original_link.apply(this, arguments);
        };
    };

    return $delegate;
}

module.exports = DatepickerPopupDirectiveDecorator;