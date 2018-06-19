const Page = require('./page');

module.exports = class LeaveAbsenceDashboard extends Page {
  /**
   * Opens one of the leave absence dashboard tabs
   *
   * @param  {String} tabId
   * @return {Object} resolves with the tab page object
   */
  async openTab (tabId) {
    const Tab = await require('./tabs/' + tabId);
    const tab = new Tab(this.puppet, false);

    await tab.init();
    await this.puppet.click('[ui-sref="' + tab.tabUiSref + '"]');
    await this.puppet.waitFor(tab.readySelector, { visible: true });
    await this.puppet.waitFor(500);

    return tab;
  }
};
