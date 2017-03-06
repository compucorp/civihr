var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {

  return page.extend({

    /**
     * [openManageRightsModal description]
     * @return {[type]} [description]
     */
    openManageRightsModal: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('#manage-roles-and-teams');
        casper.waitWhileVisible('.crm_spinner');
      });
    },

    /**
     * [openTab description]
     * @param  {[type]}   tabId [description]
     * @return {[type]}         [description]
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
     * [showActions description]
     * @return {[type]} [description]
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
