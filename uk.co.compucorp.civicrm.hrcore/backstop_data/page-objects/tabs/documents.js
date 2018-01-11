var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    tabTitle: 'Documents',
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
      var casper = this.casper;

      casper.waitUntilVisible('form[name="formDocuments"]');
      casper.waitWhileVisible('.ct-spinner-cover');

      return true;
    }
  });
})();
