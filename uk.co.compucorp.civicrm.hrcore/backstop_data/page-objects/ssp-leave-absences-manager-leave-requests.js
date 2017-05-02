var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {

  return page.extend({
    /**
     * Wait for the page to be ready
     * @return {Object} this object
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.waitUntilVisible('tbody  tr:nth-child(1) a');
      });

      return this;
    },
    /**
     * Opens the given menu item from side menu
     * @param {String} menuIndex
     * @return {Object} this object
     */
    openSideMenu: function (menuIndex) {
      var casper = this.casper;

      casper.then(function () {
        casper.waitUntilVisible('ul.chr_vertical_tabs li:nth-child(1) a', function () {
          casper.click('ul.chr_vertical_tabs li:nth-child(' + menuIndex + ') a');
          casper.waitUntilVisible('ul.chr_vertical_tabs li:nth-child(1) a');
        });
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
      // || was not working in string so created another variable here
      var selectedRow = row || 1;

      casper.then(function () {
        casper.waitUntilVisible('tr:nth-child(1) > td > div > a', function () {
          casper.click('tr:nth-child('+ selectedRow +') > td > div > a');
        });
      });

      return this;
    },
    /**
     * Takes screenshot with given name
     * @param {String} name of the screenshot
     * @return {Object} this object
     */
    takeScreenShot: function (name) {
      var casper = this.casper;

      casper.then(function () {
        casper.capture(name);
      });

      return this;
    }
  });
})();
