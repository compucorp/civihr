var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Wait for the page to be ready
     */
    waitForReady: function () {
      this.waitUntilVisible('.chr_leave-balance-report');
    }
  });
})();
