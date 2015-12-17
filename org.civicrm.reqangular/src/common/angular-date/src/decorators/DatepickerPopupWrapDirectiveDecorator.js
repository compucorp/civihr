function DatepickerPopupWrapDirectiveDecorator($delegate) {
    var directive = $delegate[0];

    directive.templateUrl = 'templates/datepickerPopup.html';

    return $delegate;
}

module.exports = DatepickerPopupWrapDirectiveDecorator;