var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {

  return page.extend({

    /**
     * [openMyReport description]
     * @return {[Page]} [description]
     */
    openMyReport: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.waitUntilVisible('td[ng-click="report.toggleSection(\'pending\')"]', function () {
          casper.wait(700);
        });
      });

      return this;
    },

    /**
     * [openMyReportSection description]
     */
    openMyReportSection: function (section) {
      var casper = this.casper;

      casper.then(function () {
        casper.click('td[ng-click="report.toggleSection(' + section + ')"]');
      });
    },

    /**
     * [openActionsMyReport description]
     */
    openActionsMyReport: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.waitForSelector('tr:nth-child(1)  div[uib-dropdown] a:nth-child(1)', function () {
          casper.click('tr:nth-child(1)  div[uib-dropdown] a:nth-child(1)');
        });
      });
    },

    /**
     * [editRequestForMyReport description]
     * @return {[Promise]} [description]
     */
    editRequestForMyReport: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('body > ul.dropdown-menu:nth-of-type(1) li[ng-repeat]:first-child a');
          casper.wait(4000);
          resolve(this.waitForModal('leave-request'));
        }.bind(this));
      }.bind(this));
    },
  });
})();
