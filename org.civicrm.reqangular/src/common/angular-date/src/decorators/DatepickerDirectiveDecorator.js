function DatepickerDirectiveDecorator($delegate) {
    var directive = $delegate[0];

    directive.controller = "DatePickerController";

    return $delegate;
}

module.exports = DatepickerDirectiveDecorator;