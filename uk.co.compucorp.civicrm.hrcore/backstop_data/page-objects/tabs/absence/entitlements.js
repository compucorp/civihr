var tab = require('../tab');

module.exports = tab.extend({
  tabTitle: 'Entitlements',

  /**
   * Overrides the original tab's `waitForReady` method
   * There is no single selector that can be used as `readySelector` (which
   * would be used by the original `waitForReady` method) to detect when the
   * tab is ready, so as a quick workaround we simply override the method
   * and perform all the needed checks in it
   */
  waitForReady: function () {
    this.chromy.wait('contract-entitlements');
    this.chromy.wait('annual-entitlements');
    // Waits for spinners to hide which indicates the load of the data
    this.chromy.wait(function () {
      // = CasperJS.waitWhileVisible()
      var dom = document.querySelector('contract-entitlements .spinner');

      return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
    });
    this.chromy.wait(function () {
      // = CasperJS.waitWhileVisible()
      var dom = document.querySelector('annual-entitlements .spinner');

      return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
    });
  }
});
