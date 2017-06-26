var page = require('../../contact-summary');

module.exports = (function () {
  return page.extend({
    /**
     * Wait for the page to be ready
     */
    waitForReady: function () {
      var casper = this.casper;
      // Opens the Entitlements tab
      casper.click('.absence-tab-page .uib-tab[data-tabname=entitlements]>a');
      // Waits for tables to appear which indicates the load of the components
      casper.waitUntilVisible('contract-entitlements');
      casper.waitUntilVisible('annual-entitlements');
      // Waits for spinners to hide which indicates the load of the data
      casper.waitWhileVisible('contract-entitlements .chr_spinner');
      casper.waitWhileVisible('annual-entitlements .chr_spinner');
    }
  });
})();
