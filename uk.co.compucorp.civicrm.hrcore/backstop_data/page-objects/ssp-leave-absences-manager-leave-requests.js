var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {

  return page.extend({
    /**
     * Wait for the page to be ready as it waits for the actions of the first
     * row of leave requests to be visible
     * @return {Object} this object
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.waitUntilVisible('tbody tr:nth-child(1) a');
      });

      return this;
    },
    /**
     * Opens the dropdown for manager actions like edit/respond, cancel.
     * @param {Number} row number corresponding to leave request in the list
     * @return {Object} this object
     */
    openActionsForRow: function (row) {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.chr_manager_dashboard__panel_body tr:nth-child('+ (row || 1) +') .dropdown-toggle');
      });

      return this;
    },
    /**
     * Expands filters on screen
     * @return {Object} this object
     */
    expandFilter: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.chr_manager_dashboard__filter');
        casper.waitUntilVisible('.chr_manager_dashboard__sub-header div:nth-child(1)');
      });

      return this;
    }
  });
})();
