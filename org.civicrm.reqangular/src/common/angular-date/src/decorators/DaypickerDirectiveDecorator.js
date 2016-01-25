/**
 * Decorates Daypicker directive, so that it uses custom template.
 *
 * TODO: Fetch date format from CiviCRM settings
 *
 * @name DaypickerDirectiveDecorator
 *
 */

function DaypickerDirectiveDecorator($delegate) {

    /**
     * @override
     *
     * template path
     * @type {string}
     */
    $delegate[0].templateUrl = "templates/day.html";

    return $delegate;
}

module.exports = DaypickerDirectiveDecorator;