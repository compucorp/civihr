/* global Event */

var Promise = require('es6-promise').Promise;
var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Wait for the page to be ready as it waits for the actions of the first
     * row of leave requests to be visible
     * @return {Object} this object
     */
    waitForReady: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.waitUntilVisible('tbody tr:nth-child(1) a');
      });

      return this;
    },
    /**
     * Opens the dropdown for manager actions like edit/respond, cancel.
     * @param {Number} row number corresponding to leave request in the list
     * @return {Object} this object
     */
    openActionsForRow: function (row) {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.chr_manage_leave_requests__panel_body tr:nth-child(' + (row || 1) + ') .dropdown-toggle');
      });

      return this;
    },
    /**
     * Expands filters on screen
     * @return {Object} this object
     */
    expandFilter: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.chr_manage_leave_requests__filter');
        casper.waitUntilVisible('.chr_manage_leave_requests__sub-header div:nth-child(1)');
      });

      return this;
    },
    /**
     * Opens leave type filter
     * @param {Number} leaveType index like 1 for Holiday/Vacation, 2 for TOIL, 3 for Sickness
     * @return {Object} this object
     */
    openLeaveTypeFor: function (leaveType) {
      var casper = this.casper;

      casper.then(function () {
        casper.evaluate(function (leaveType) {
          var element = document.querySelector('.chr_manage_leave_requests__header div:nth-child(1) > select');
          element.selectedIndex = leaveType;// for TOIL option
          element.dispatchEvent(new Event('change'));
        }, leaveType);
      });

      return this;
    },
    /**
     * User clicks on the edit/respond action
     * @param {Number} row number corresponding to leave request in the list
     * @return {Promise}
     */
    editRequest: function (row) {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('body > ul.dropdown-menu:nth-of-type(' + (row || 1) + ') li:first-child a');
          // as there are multiple spinners it takes more time to load up
          resolve(this.waitForModal('ssp-leave-request', '.chr_leave-request-modal__form'));
        }.bind(this));
      }.bind(this));
    },
    /**
     * Apply leave on behalf of staff
     * @param {String} row number corresponding to leave request in the list like leave, sickness or toil
     * @return {Promise}
     */
    applyLeaveForStaff: function (leaveType) {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          var selector = '.button-container button:nth-child(' + (leaveType === 'leave' ? 1 : 2) + ')';

          casper.click(selector);
        });

        casper.then(function () {
          if (leaveType === 'sickness') {
            casper.click('.button-container li:nth-child(1) a');
          } else if (leaveType === 'toil') {
            casper.click('.button-container li:nth-child(2) a');
          }
        });

        casper.then(function () {
          // as there are multiple spinners it takes more time to load up
          resolve(this.waitForModal('ssp-leave-request', '.chr_leave-request-modal__form'));
        }.bind(this));
      }.bind(this));
    }
  });
})();
