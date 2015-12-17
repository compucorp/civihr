function DaypickerDirectiveDecorator($delegate) {
    var directive = $delegate[0];

    directive.templateUrl = "templates/day.html";

    return $delegate;
}

module.exports = DaypickerDirectiveDecorator;