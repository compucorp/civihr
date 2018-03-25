const page = require('./page');

module.exports = page.extend({
  /**
   * Wait for the page to be ready
   */
  async waitForReady () {
    await this.puppet.waitFor('.chr_leave-balance-tab', { visible: true });
  }
});
