var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Wait for the page to be ready
     * @return {Object} this object
     */
    waitForReady: function () {
      this.waitUntilVisible('td[ng-click="report.toggleSection(\'pending\')"]');
    },
    /**
     * Opens the given section of my report pageName
     * @param {String} section
     * @return {Object} this object
     */
    openSection: function (section) {
      var casper = this.casper;

      casper.then(function () {
        casper.click('td[ng-click="report.toggleSection(\'' + section + '\')"]');
        casper.waitWhileVisible('.spinner');
      });

      return this;
    },
    /**
     * Opens the dropdown for staff actions like edit/respond, cancel.
     * @param {Number} row number corresponding to leave request in the list
     * @return {Object} this object
     */
    openActionsForRow: function (row) {
      var casper = this.casper;

      casper.then(function () {
        return casper.waitForSelector('tr:nth-child(1) div[uib-dropdown] a:nth-child(1)');
      }).then(function () {
        casper.click('div:nth-child(2) tr:nth-child(' + (row || 1) + ') div[uib-dropdown] a:nth-child(1)');
      })

      return this;
    },

    /**
     * User clicks on the edit/respond action
     * @param {Number} row number corresponding to leave request in the list
     * @return {Promise}
     */
    editRequest: function (row) {
      var casper = this.casper;

      casper.then(function () {
        casper.click('body > ul.dropdown-menu:nth-of-type(' + (row || 1) + ') li:first-child a');
        // As there are multiple spinners it takes more time to load up
        casper.waitWhileVisible('.modal-content .spinner:nth-child(1)');
        casper.waitWhileVisible('leave-request-popup-details-tab .spinner');
      });

      return this;
    },

    openCommentsTab: function () {
      var selector = '.chr_leave-request-modal__tab .uib-tab:nth-of-type(2) a';

      this.casper.waitForSelector(selector)
        .then(function () {
          this.casper.click(selector);
        }.bind(this));

      return this;
    },

    /**
     * Triggers an error alert
     * by selecting a date that does not correspond to any Absence Period
     *
     * @return {Promise}
     */
    triggerErrorAlert: function () {
      var casper = this.casper;
      var modalSelector = '.chr_leave-request-modal';
      var datepickerSelector = modalSelector + ' .uib-datepicker-popup';
      var previousMonthButtonSelector = datepickerSelector + ' .fa-chevron-left';

      casper.then(function () {
        casper.click(modalSelector + ' [uib-datepicker-popup]');

        return casper.waitForSelector(previousMonthButtonSelector);
      }).then(function () {
        // Go 10 years back by clicking "<" button, Absence Period won't exist for this date
        for (var i = 0; i < 12 * 10; i++) {
          casper.click(previousMonthButtonSelector);
        }
        // Trigger the error alert by finally selecting day
        casper.click(datepickerSelector + ' .uib-day button');
      });

      return this;
    }
  });
})();
