function DatepickerPopupWrapDirectiveDecorator($delegate) {

    $delegate[0].templateUrl = 'templates/datepickerPopup.html';

    return $delegate;
}

module.exports = DatepickerPopupWrapDirectiveDecorator;