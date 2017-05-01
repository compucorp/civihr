var Promise = require('es6-promise').Promise;
var page = require('./page');

var currentMonth = (new Date()).getMonth() + 1;

module.exports = (function () {

  return page.extend({
    /**
     * Wait for the page to be ready
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.waitUntilVisible('.panel.panel-default:nth-of-type(' + currentMonth + ') .chr_leave-calendar__month-body');
      });
    },
    /**
     * Clear the months selected to show all months
     */
    showAllMonths: function () {
      var casper = this.casper;
      //If Current month is december then need to wait for November as it would be loaded last when all months load
      var lastMonth = currentMonth === 12 ? 11 : 12;

      casper.then(function () {
        casper.click('.panel-heading .close.ui-select-match-close');
        casper.waitUntilVisible('.panel.panel-default:nth-of-type(' + lastMonth + ') .chr_leave-calendar__month-body');
      });
    }
  });
})();
