function DaypickerDirectiveDecorator($delegate) {

    $delegate[0].templateUrl = "templates/day.html";

    return $delegate;
}

module.exports = DaypickerDirectiveDecorator;