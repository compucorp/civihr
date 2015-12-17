function DatepickerDirectiveDecorator($delegate) {
    var directive = $delegate[0];

    var old_link = directive.link;

    delete directive.link;

    directive.compile = function(){
        return function(scope, element, attrs, ctrls){

            /**
             * @override
             * @type {number}
             */
            ctrls[0].startingDay = 1;

            old_link(scope, element, attrs, ctrls);
        };
    };

    return $delegate;
}

module.exports = DatepickerDirectiveDecorator;