const Tab = require('../tab');

module.exports = class AbsenceEntitlementsTab extends Tab {
  constructor () {
    super(...arguments);
    this.tabTitle = 'Entitlements';
  }

  /**
   * Overrides the original tab's `waitForReady` method
   * There is no single selector that can be used as `readySelector` (which
   * would be used by the original `waitForReady` method) to detect when the
   * tab is ready, so as a quick workaround we simply override the method
   * and perform all the needed checks in it
   */
  async waitForReady () {
    await this.puppet.waitFor('contract-entitlements');
    await this.puppet.waitFor('annual-entitlements');
    // Waits for spinners to hide which indicates the load of the data
    await this.puppet.waitFor('contract-entitlements .spinner', { hidden: true });
    await this.puppet.waitFor('annual-entitlements .spinner', { hidden: true });
  }
};
