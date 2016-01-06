/**
 * Decorates DatepickerPopupWrap directive, so that it uses custom template.
 *
 * TODO: Fetch date format from CiviCRM settings
 *
 * @name DatepickerPopupWrapDirectiveDecorator
 *
 */

function DatepickerPopupWrapDirectiveDecorator($delegate) {

    /**
     * @override
     *
     * template path
     * @type {string}
     */
    $delegate[0].templateUrl = 'templates/datepickerPopup.html';

    return $delegate;
}

module.exports = DatepickerPopupWrapDirectiveDecorator;