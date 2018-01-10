var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: 'form[name="formDocuments"]',
    tabTitle: 'Documents',
    /**
     * Addional logic to Wait for the tab to load
     *
     * @return {promise}
     */
    waitForTabLoad: function () {
      var casper = this.casper;

      return casper.waitWhileVisible('.ct-spinner-cover');
    }
  });
})();
