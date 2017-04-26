var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {

  return page.extend({
    /**
     * [init page]
     */
    initPage: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.wait(10000);
          resolve();
        });
      });
    },
    /**
     * [when all months visible]
     */
    allMonths: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.panel-heading .close.ui-select-match-close');
        casper.waitUntilVisible('.panel.panel-default:nth-of-type(12) ng-include');
      });
    }
  });
})();
