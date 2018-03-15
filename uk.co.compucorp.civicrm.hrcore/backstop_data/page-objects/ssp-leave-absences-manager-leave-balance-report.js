var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Wait for the page to be ready
     */
    waitForReady: function () {
      this.chromy.waitUntilVisible('.chr_leave-balance-tab');
    }
  });
})();
