function DaypickerDirectiveDecorator($delegate) {
    var directive = $delegate[0];

    directive.templateUrl = "directives/day.html";

    return $delegate;
}

module.exports = DaypickerDirectiveDecorator;