var Promise = require('es6-promise').Promise;
var tab = require('./tab');

module.exports = tab.extend({
  readySelector: '.absence-tab-page',
  tabTitle: 'Absence',

  /**
   * Opens one of the absence sub tabs
   *
   * @param  {string} tabId
   * @return {object} resolves with the tab page object
   */
  openSubTab: function (tabId) {
    return new Promise(function (resolve) {
      var tab = require('./absence/' + tabId);

      this.chromy.click('[heading="' + tab.tabTitle + '"] > a');
      resolve(tab.init(this.chromy, false));
    }.bind(this));
  }
});
