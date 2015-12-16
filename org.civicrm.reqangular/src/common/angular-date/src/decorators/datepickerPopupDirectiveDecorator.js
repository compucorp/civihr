function DatepickerPopupDirectiveDecorator($delegate) {
    var directive = $delegate[0];
    var original_link = directive.link;


    // FIXME noassign error is caused by wrong binding - change it here
    // FIXME as for now i have no clue how to solve it
    //directive.scope.isOpen = '&';
    //console.log(directive.scope);

    /**
     * Implements original link function
     * @returns Function
     */
    directive.compile = function(){
        return function(scope, element, attrs, ngModel){

            // TODO fetch form civicrm settings
            /**
             * @override
             * @type {string}
             */
            attrs.datepickerPopup = 'dd/MM/yyyy';

            original_link(scope, element, attrs, ngModel);
        };
    };

    // link function is called by compile
    delete directive.link;

    return $delegate;
}

module.exports = DatepickerPopupDirectiveDecorator;