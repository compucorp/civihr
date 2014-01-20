// A collection of helper functions for working with qUnit

var assertContainsText = function(expectedText, $el, message) {
  var found = $el.text().contains(expectedText);
  if (!found && console.log) {
    console.log('assertContainsText - failure', expectedText, $el.text())
  }
  ok(found, message);
};