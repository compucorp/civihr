var Promise = require('es6-promise').Promise;
var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: '.absence-tab-page',
    tabTitle: 'Absence',

    /**
     * Opens one of the absence sub tabs
     *
     * @param  {string} tabId
     * @return {object} resolves with the tab page object
     */
    openSubTab: function (tabId) {
      var casper = this.casper;
      var tab = require('../absence-tabs/' + tabId).init(casper, false);

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('[heading="' + tab.tabTitle + '"] > a');
          casper.waitFor(tab.ready.bind(tab), function () {
            casper.wait(500);
            resolve(tab);
          });
        });
      });
    }
  });
})();
