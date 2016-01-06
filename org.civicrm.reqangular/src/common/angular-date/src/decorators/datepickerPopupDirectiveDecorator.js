/**
 * Decorates DatepickerPopup directive, so that it uses monday as a first day of the week.
 *
 * TODO: Fetch date format from CiviCRM settings
 *
 * @name DatepickerPopupDirectiveDecorator
 *
 */

function DatepickerPopupDirectiveDecorator($delegate) {
    var original_link = $delegate[0].link;

    $delegate[0].compile = function(){

        /**
         * Compile returns a link function.
         * @override
         */
        return function(scope, element, attrs, ngModel){

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