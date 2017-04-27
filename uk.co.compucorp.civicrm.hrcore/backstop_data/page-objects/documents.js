var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  var documentSelector = '.ct-table-documents > tbody > tr:nth-child(1)';

  return page.extend({

    /**
     * Opens the modal to add a documents
     *
     * @return {Promise} resolves with the document modal page object
     */
    addDocument: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('a[ng-click*="itemAdd"]');
          resolve(this.waitForModal('document'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * Shows the advanced filters
     *
     * @return {object}
     */
    advancedFilters: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('a[ng-click*="isCollapsed.filterAdvanced"]');
        casper.wait(500);
      });

      return this;
    },

    /**
     * Shows the dropdown of the actions available on any given document
     *
     * @return {object}
     */
    documentActions: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(documentSelector + ' .ct-context-menu-toggle');
      });

      return this;
    },

    /**
     * Opens a document
     *
     * @return {Promise} resolves with the document modal page object
     */
    openDocument: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          this.documentActions();
        }.bind(this));

        casper.then(function () {
          casper.click(documentSelector + ' .dropdown-menu a[ng-click*="modalDocument"]');
          resolve(this.waitForModal('document'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * Shows the "select dates" filter
     */
    selectDates: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.ct-select-dates');
        casper.wait(500);
      });
    },

    /**
     * Waits until the specified select is visible on the page
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.waitUntilVisible('.ct-container-inner', function () {
        casper.wait(2000);
      });
    }
  });
})();
