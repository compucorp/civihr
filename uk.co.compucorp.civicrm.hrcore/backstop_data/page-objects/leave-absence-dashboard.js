const page = require('./page');

module.exports = page.extend({
  /**
   * Opens one of the leave absence dashboard tabs
   *
   * @param  {String} tabId
   * @return {Object} resolves with the tab page object
   */
  async openTab (tabId) {
    const tab = await require('./tabs/' + tabId).init(this.puppet, false);

    await this.puppet.click('[ui-sref="' + tab.tabUiSref + '"]');
    await this.puppet.waitFor(tab.readySelector, { visible: true });
    await this.puppet.waitFor(500);

    return tab;
  }
});
