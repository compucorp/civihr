var tab = require('../tabs/tab');

module.exports = (function () {
  return tab.extend({
    tabTitle: 'Entitlements',

    /**
     * Overrides the original tab's `ready` method
     * There is no single selector that can be used as `readySelector` (which
     * would be used by the original `ready` method) to detect when the
     * tab is ready, so as a quick workaround we simply override the method
     * and perform all the needed checks in it
     *
     * @return {Boolean} returns `true` for the `casper.waitFor()` caller
     */
    ready: function () {
      this.casper.waitUntilVisible('contract-entitlements');
      this.casper.waitUntilVisible('annual-entitlements');
      // Waits for spinners to hide which indicates the load of the data
      this.casper.waitWhileVisible('contract-entitlements .chr_spinner');
      this.casper.waitWhileVisible('annual-entitlements .chr_spinner');

      return true;
    }
  });
})();
