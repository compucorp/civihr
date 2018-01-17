var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  var contactActionsMenuButton = '#crm-contact-actions-link';
  var deleteUserAccountButton = '[data-delete-user-url]';

  return page.extend({

    /**
     * Clicks the contact action menu button
     */
    openContactActionMenu: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click(contactActionsMenuButton);
          casper.wait(5000);
        });
      });
    },

    /**
     * Clicks the contact action menu > delete user account button
     */
    deleteUserAccount: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(contactActionsMenuButton);
        casper.wait(5000);
        casper.click(deleteUserAccountButton);
        casper.wait(5000);
      });

      return this;
    }
  });
})();
