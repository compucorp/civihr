const page = require('./page');

const documentSelector = '.ct-table-documents > tbody > tr:nth-child(1)';

module.exports = page.extend({
  /**
   * Adds a document by opening the modal
   *
   * @return {Object} the document modal page object
   */
  async addDocument () {
    await this.puppet.click('a[ng-click*="itemAdd"]');

    return this.waitForModal('document');
  },

  /**
   * Shows the advanced filters
   *
   * @return {Object}
   */
  async advancedFilters () {
    await this.puppet.click('a[ng-click*="isCollapsed.filterAdvanced"]');
    await this.puppet.waitFor(500);
  },

  /**
   * Shows the dropdown of the actions available on any given document
   *
   * @return {Object}
   */
  async documentActions () {
    await this.puppet.click(documentSelector + ' .ct-context-menu-toggle');
  },

  /**
   * Opens a document
   *
   * @return {Object} the document modal page object
   */
  async openDocument () {
    await this.documentActions();
    await this.puppet.click(documentSelector + ' .dropdown-menu a[ng-click*="modalDocument"]');

    return this.waitForModal('document');
  },

  /**
   * Shows the "select dates" filter
   */
  async selectDates () {
    await this.puppet.click('.ct-select-dates');
    await this.puppet.waitFor(500);
  },

  /**
   * Waits until the user name in the "Staff" column and the filter dates are visible
   */
  async waitForReady () {
    await this.puppet.waitFor('.ct-filter-date', { visible: true });
    await this.puppet.waitFor('.ct-table-documents [href^="/civicrm/contact/view"]', { visible: true });
    // For some reason Puppetteer considers the user name visible even when it
    // isn't really yet, this slight delay allows the element to be fully visible
    // before taking the screenshots
    await this.puppet.waitFor(500);
  }
});
