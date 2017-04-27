var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {

  return page.extend({
    /**
     * Wait for the page to be ready
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.waitUntilVisible('td[ng-click="report.toggleSection(\'pending\')"]');
      });
    },
    /**
     * Opens the given section of my report pageName
     * @param {String} section
     */
    openSection: function (section) {
      var casper = this.casper;

      casper.then(function () {
        casper.click('td[ng-click="report.toggleSection(' + section + ')"]');
      });
    },
    /**
     * Opens the dropdown for staff actions like edit/respond, cancel.
     */
    openActions: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.waitForSelector('tr:nth-child(1)  div[uib-dropdown] a:nth-child(1)', function () {
          casper.click('tr:nth-child(1)  div[uib-dropdown] a:nth-child(1)');
        });
      });
    },

    /**
     * User clicks on the edit/respond action
     * @return {Promise}
     */
    editRequest: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('body > ul.dropdown-menu:nth-of-type(1) li[ng-repeat]:first-child a');
          //as there are multiple spinners it takes more time to load up
          resolve(this.waitForModal('ssp-leave-request', '.chr_leave-request-modal__form'));
        }.bind(this));
      }.bind(this));
    },
  });
})();
