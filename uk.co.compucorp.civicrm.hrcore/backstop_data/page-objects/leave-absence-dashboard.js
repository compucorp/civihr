var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  return page.extend({

    /**
     * Opens one of the leave absence dashboard tabs
     *
     * @param  {string} tabId
     * @return {object} resolves with the tab page object
     */
    openTab: function (tabId) {
      var casper = this.casper;
      var tab = require('./tabs/' + tabId).init(casper, false);

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('[ui-sref="' + tab.tabUiSref + '"]');
          casper.waitFor(tab.ready.bind(tab), function () {
            casper.wait(500);
            resolve(tab);
          });
        });
      });
    }
  });
})();
