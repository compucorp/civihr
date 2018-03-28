var Promise = require('es6-promise').Promise;
var page = require('./page');

var documentSelector = '.ct-table-documents > tbody > tr:nth-child(1)';

module.exports = page.extend({
  /**
   * Adds a document by opening the modal
   *
   * @return {Promise} resolves with the document modal page object
   */
  addDocument: function () {
    return new Promise(function (resolve) {
      this.chromy.click('a[ng-click*="itemAdd"]');

      resolve(this.waitForModal('document'));
    }.bind(this));
  },

  /**
   * Shows the advanced filters
   *
   * @return {Object}
   */
  advancedFilters: function () {
    this.chromy.click('a[ng-click*="isCollapsed.filterAdvanced"]');
    this.chromy.wait(500);

    return this;
  },

  /**
   * Shows the dropdown of the actions available on any given document
   *
   * @return {Object}
   */
  documentActions: function () {
    this.chromy.click(documentSelector + ' .ct-context-menu-toggle');

    return this;
  },

  /**
   * Opens a document
   *
   * @return {Promise} resolves with the document modal page object
   */
  openDocument: function () {
    return new Promise(function (resolve) {
      this.documentActions();

      this.chromy.click(documentSelector + ' .dropdown-menu a[ng-click*="modalDocument"]');
      resolve(this.waitForModal('document'));
    }.bind(this));
  },

  /**
   * Shows the "select dates" filter
   */
  selectDates: function () {
    this.chromy.click('.ct-select-dates');
    this.chromy.wait(500);
  },

  /**
   * Waits until the specified select is visible on the page
   */
  waitForReady: function () {
    this.chromy.waitUntilVisible('.ct-filter-date');
  }
});
