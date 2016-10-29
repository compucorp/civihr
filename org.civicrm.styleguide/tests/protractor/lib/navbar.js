var prtr = require('./protractor-util');

/**
 * The CiviCRM navbar waits for the mouse to hover before exposing nested elements.
 * This constant specifies the #milliseconds to hover an element before moving
 * on to the next child.
 *
 * This is likely to be a systemic performance drag on the test-suite...
 *
 * @type {number}
 */
var HOVER_DELAY = 250;

module.exports = {

  /**
   * Navigate to an item using the global CiviCRM navbar.
   *
   * Ex: NavBar.goto(['Support', 'Developer', 'API Explorer']);
   *
   * @param parts string|array
   */
  goto: function(parts) {
    prtr.withoutAngular(function() {
      if (typeof parts === 'string') {
        parts = [parts];
      }

      var navItem = element(by.cssContainingText('#civicrm-menu li', parts[0]));
      browser.actions().mouseMove(navItem).click().perform();

      if (parts.length === 1) {
        return;
      }

      for (var i = 1; i < parts.length - 1; i++) {
        navItem = element(by.cssContainingText('.menu-item span', parts[i]));
        expect(navItem.getText()).toBe(parts[i]);
        browser.actions().mouseMove(navItem).perform();
        browser.sleep(HOVER_DELAY);
      }

      navItem = element(by.cssContainingText('.menu-item a', parts[parts.length - 1]));
      navItem.click()
    });
  }

};