var page = require('./page');

module.exports = page.extend({
  /**
   * Wait for the page to be ready
   */
  waitForReady: function () {
    this.chromy.waitUntilVisible('.chr_leave-balance-tab');
    this.chromy.wait(function () {
      // = CasperJS.waitWhileVisible()
      var spinners = document.querySelectorAll('.spinner');

      return Array.prototype.every.call(spinners, function (dom) {
        return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
      });
    });
  }
});
