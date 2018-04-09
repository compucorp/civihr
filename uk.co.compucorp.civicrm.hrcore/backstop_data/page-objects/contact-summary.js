const page = require('./page');

module.exports = page.extend({
  /**
   * Opens the "contact access rights" modal
   *
   * @return {Object} the modal page object
   */
  async openManageRightsModal () {
    await this.showActions();
    await this.puppet.click('[data-contact-access-rights]');
    await this.puppet.waitFor('.spinner', { hidden: true });

    return this.waitForModal('contact-access-rights');
  },

  /**
   * Opens one of the contact summary tabs
   *
   * @param  {String} tabId
   * @return {Object} the tab page object
   */
  async openTab (tabId) {
    const tabObj = require('./tabs/' + tabId);
    await this.puppet.click('[title="' + tabObj.tabTitle + '"]');

    return tabObj.init(this.puppet, false);
  },

  /**
   * Shows the dropdown of the "Actions" button in the contact summary page
   */
  async showActions () {
    await this.puppet.click('#crm-contact-actions-link');
    await this.puppet.waitFor('#crm-contact-actions-list');
  },

  /**
   * Wait an arbitrary amount of time for the data to load
   */
  async waitForReady () {
    await this.puppet.waitFor(6000);
  }
});
