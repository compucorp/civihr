// A collection of helper functions for working with qUnit

var assertContainsText = function(expectedText, $el, message) {
  var found = $el.text().contains(expectedText);
  if (!found && console.log) {
    console.log('assertContainsText - failure', expectedText, $el.text())
  }
  ok(found, message);
};

// Assert that two strings are equivalent, not-with-standing whitespace
// actual and expected are string literals; or else jQuery DOM objects
var assertLike = function(actual, expected) {
  var clean = function(c) { return c.replace(/\s+/g,' ').trim(); }
  if (actual.text) { actual = actual.text(); }
  if (expected.text) { expected = expected.text(); }
  equal(clean(actual), clean(expected));
}