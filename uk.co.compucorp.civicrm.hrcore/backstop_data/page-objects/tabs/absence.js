const Tab = require('./tab');

module.exports = class AbsenceTab extends Tab {
  constructor () {
    super(...arguments);

    this.readySelector = '.absence-tab-page';
    this.tabTitle = 'Absence';
  }

  /**
   * Opens one of the absence sub tabs
   *
   * @param {String} tabId
   */
  async openSubTab (tabId) {
    const Tab = require('./absence/' + tabId);
    const tab = new Tab(this.puppet, false);

    await this.puppet.click('[heading="' + tab.tabTitle + '"] > a');
    await tab.init();

    return tab;
  }
};
