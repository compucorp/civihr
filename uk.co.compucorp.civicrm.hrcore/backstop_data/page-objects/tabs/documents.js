const tab = require('./tab');

module.exports = tab.extend({
  tabTitle: 'Documents',
  /**
   * Overrides the original tab's `waitForReady` method
   * There is no single selector that can be used as `readySelector` (which
   * would be used by the original `waitForReady` method) to detect when the
   * tab is ready, so as a quick workaround we simply override the method
   * and perform all the needed checks in it
   */
  async waitForReady () {
    await this.puppet.waitFor('form[name="formDocuments"]', { visible: true });
    await this.puppet.waitFor('.ct-spinner-cover', { hidden: true });
  }
});
