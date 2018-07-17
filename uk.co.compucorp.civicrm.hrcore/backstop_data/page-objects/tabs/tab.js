const Page = require('../page');

module.exports = class Tab extends Page {
  /**
   * Defines that the tab is ready when the a specific selector is visible
   */
  async waitForReady () {
    await this.puppet.waitFor(this.readySelector, { visible: true });
  }
};
