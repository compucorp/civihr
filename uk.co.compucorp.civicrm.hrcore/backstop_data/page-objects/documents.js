var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  var documentSelector = '.ct-table-documents > tbody > tr:nth-child(1)';

  return page.extend({

    /**
     * [addDocument description]
     */
    addDocument: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('a[ng-click*="itemAdd"]');
          resolve(this.waitForModal('document'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * [advancedFilters description]
     * @return {[type]} [description]
     */
    advancedFilters: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('a[ng-click*="isCollapsed.filterAdvanced"]');
        casper.wait(500);
      });

      return this;
    },

    /**
     * [documentActions description]
     * @return {[type]} [description]
     */
    documentActions: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click(documentSelector + ' .ct-context-menu-toggle');
      });

      return this;
    },

    /**
     * [openDocument description]
     * @return {[type]} [description]
     */
    openDocument: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          this.documentActions();
        }.bind(this));

        casper.then(function () {
          casper.click(documentSelector + ' .dropdown-menu a[ng-click*="modalDocument"]');
          resolve(this.waitForModal('document'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * [selectDates description]
     * @return {[type]} [description]
     */
    selectDates: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.ct-select-dates');
        casper.wait(500);
      });
    },

    /**
     * [waitForReady description]
     * @return {[type]} [description]
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.waitUntilVisible('.ct-container-inner', function () {
        casper.wait(2000);
      });
    }
  });
})();
