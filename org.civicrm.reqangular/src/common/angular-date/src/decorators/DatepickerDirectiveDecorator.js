function DatepickerDirectiveDecorator($delegate) {
    var old_link = $delegate[0].link;

    $delegate[0].compile = function(){
        return function(scope, element, attrs, ctrls){

            /**
             * @override
             * @type {number}
             */
            ctrls[0].startingDay = 1;

            old_link.apply(this, arguments);
        };
    };

    return $delegate;
}

module.exports = DatepickerDirectiveDecorator;