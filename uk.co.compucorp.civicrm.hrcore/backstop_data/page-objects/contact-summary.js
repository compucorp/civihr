const Page = require('./page');

module.exports = class ContactSummary extends Page {
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
  }

  /**
   * Opens one of the contact summary tabs
   *
   * @param  {String} tabId
   * @return {Object} the tab page object
   */
  async openTab (tabId) {
    const Tab = require('./tabs/' + tabId);
    const tab = new Tab(this.puppet, false);

    await this.puppet.click('[title="' + tab.tabTitle + '"]');
    await tab.init();

    return tab;
  }

  /**
   * Shows the dropdown of the "Actions" button in the contact summary page
   */
  async showActions () {
    await this.puppet.click('#crm-contact-actions-link');
    await this.puppet.waitFor('#crm-contact-actions-list');
  }

  /**
   * Wait an arbitrary amount of time for the data to load, then waits
   * for all the spinners to disappear
   */
  async waitForReady () {
    await this.puppet.waitFor('#contactsummary', { visible: true });
    await this.puppet.waitFor(6000);
    await this.puppet.waitFor(function () {
      const spinners = document.querySelectorAll('.spinner');

      return Array.prototype.every.call(spinners, function (dom) {
        return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
      });
    });
  }
};
