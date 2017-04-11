var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {

  return page.extend({

    /**
     * Opens the "contact access rights" modal
     *
     * @return {Promise} resolves with the modal page object
     */
    openManageRightsModal: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('#manage-roles-and-teams');
          casper.waitWhileVisible('.crm_spinner');
          resolve(this.waitForModal('contact-access-rights'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * Opens one of the contact summary tabs
     *
     * @param  {string} tabId
     * @return {object} resolves with the tab page object
     */
    openTab: function (tabId) {
      var casper = this.casper;
      var tab = require('./tabs/' + tabId).init(casper, false);

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('[title="' + tab.tabTitle + '"]');
          casper.waitFor(tab.ready.bind(tab), function () {
            casper.wait(500);
            resolve(tab);
          });
        });
      });
    },

    /**
     * Shows the dropdown of the "Actions" button in the contact summary page
     */
    showActions: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('#crm-contact-actions-link');
        casper.waitUntilVisible('#crm-contact-actions-list');
      });
    }
  });
})();
