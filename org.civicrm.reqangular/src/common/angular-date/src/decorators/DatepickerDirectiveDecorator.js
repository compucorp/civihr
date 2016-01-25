/**
 * Decorates Datepicker directive, so that it uses monday as a first day of the week.
 *
 * TODO in the future: Create ability to change that through CiviHR settings
 * @name DatepickerDirectiveDecorator
 *
 */

function DatepickerDirectiveDecorator($delegate) {
    var old_link = $delegate[0].link;

    $delegate[0].compile = function(){

        /**
         * Compile returns a link function.
         * @override
         */
        return function(scope, element, attrs, ctrls){

            /**
             * @type {number}
             * @description Day of the week: 0 - Sunday, 1 - Monday ... 6 - Saturday
             * @override
             */
            ctrls[0].startingDay = 1;

            old_link.apply(this, arguments);
        };
    };

    return $delegate;
}

module.exports = DatepickerDirectiveDecorator;