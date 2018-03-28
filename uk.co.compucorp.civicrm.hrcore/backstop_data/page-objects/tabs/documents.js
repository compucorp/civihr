var tab = require('./tab');

module.exports = tab.extend({
  tabTitle: 'Documents',
  /**
   * Overrides the original tab's `waitForReady` method
   * There is no single selector that can be used as `readySelector` (which
   * would be used by the original `waitForReady` method) to detect when the
   * tab is ready, so as a quick workaround we simply override the method
   * and perform all the needed checks in it
   */
  waitForReady: function () {
    this.chromy.waitUntilVisible('form[name="formDocuments"]');
    this.chromy.wait(function () {
      // = CasperJS.waitWhileVisible()
      var dom = document.querySelector('.ct-spinner-cover');

      return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
    });
  }
});
