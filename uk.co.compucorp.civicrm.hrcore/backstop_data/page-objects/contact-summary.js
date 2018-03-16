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
      var chromy = this.chromy;

      return new Promise(function (resolve) {
        this.showActions();

        chromy.click('[data-contact-access-rights]');
        chromy.wait(function () {
          var dom = document.querySelector('.spinner');

          return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
        });

        resolve(this.waitForModal('contact-access-rights'));
      }.bind(this));
    },

    /**
     * Opens one of the contact summary tabs
     *
     * @param  {string} tabId
     * @return {object} resolves with the tab page object
     */
    openTab: function (tabId) {
      return new Promise(function (resolve) {
        var tab = require('./tabs/' + tabId);
        this.chromy.click('[title="' + tab.tabTitle + '"]');

        resolve(tab.init(this.chromy, false));
      }.bind(this));
    },

    /**
     * Shows the dropdown of the "Actions" button in the contact summary page
     */
    showActions: function () {
      this.chromy.click('#crm-contact-actions-link');
      this.chromy.wait('#crm-contact-actions-list');
    }
  });
})();
