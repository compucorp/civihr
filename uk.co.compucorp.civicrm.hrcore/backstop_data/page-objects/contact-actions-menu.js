var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  var contactActionsMenuButtonSelector = '#crm-contact-actions-link';
  var deleteUserAccountButtonSelector = '[data-delete-user-url]';

  return page.extend({

    /**
     * Click the "Actions" button on contact page
     */
    openContactActionMenu: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click(contactActionsMenuButtonSelector);
        });
      });
    },

    /**
     * Click the "Delete User Account" button in the contact action menu
     */
    deleteUserAccount: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(contactActionsMenuButtonSelector);
        casper.click(deleteUserAccountButtonSelector);
      });

      return this;
    }
  });
})();
