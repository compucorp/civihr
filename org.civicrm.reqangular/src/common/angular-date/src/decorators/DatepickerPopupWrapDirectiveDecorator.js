function DatepickerPopupWrapDirectiveDecorator($delegate) {
    var directive = $delegate[0];
    var original_link = directive.link;

    directive.templateUrl = 'directives/datepickerPopup.html';

    return $delegate;
}

module.exports = DatepickerPopupWrapDirectiveDecorator;