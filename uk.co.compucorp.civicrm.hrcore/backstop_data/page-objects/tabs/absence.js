const tab = require('./tab');

module.exports = tab.extend({
  readySelector: '.absence-tab-page',
  tabTitle: 'Absence',

  /**
   * Opens one of the absence sub tabs
   *
   * @param {String} tabId
   */
  async openSubTab (tabId) {
    const tab = require('./absence/' + tabId);

    await this.puppet.click('[heading="' + tab.tabTitle + '"] > a');
    return tab.init(this.puppet, false);
  }
});
