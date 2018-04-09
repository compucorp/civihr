const page = require('./page');

module.exports = page.extend({
  /**
   * Wait for the page to be ready
   */
  async waitForReady () {
    await this.puppet.waitFor('.chr_leave-balance-tab', { visible: true });
    await this.puppet.waitFor(function () {
      var spinners = document.querySelectorAll('.spinner');

      return Array.prototype.every.call(spinners, function (dom) {
        return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
      });
    });
  }
});
