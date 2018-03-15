/* global Event */

var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Wait for the page to be ready as it waits for the actions of the first
     * row of leave requests to be visible
     *
     * @return {Object} this object
     */
    waitForReady: function () {
      this.chromy.waitUntilVisible('tbody tr:nth-child(1) a');
    },

    /**
     * Change the filter by Assignee
     *
     * @param {String} type (me|unassigned|all)
     * @return {Object} this object
     */
    changeFilterByAssignee: function (type) {
      var filters = ['me', 'unassigned', 'all'];

      this.chromy.click(
        '.chr_manage_leave_requests__assignee_filter button:nth-of-type(' +
        (filters.indexOf(type) + 1) +
        ')');
      this.chromy.waitUntilVisible('tbody tr:nth-child(1) a');

      return this;
    },

    /**
     * Opens the dropdown for manager actions like edit/respond, cancel.
     *
     * @param {Number} row number corresponding to leave request in the list
     * @return {Object} this object
     */
    openActionsForRow: function (row) {
      this.chromy.click('.chr_manage_leave_requests__panel_body tr:nth-child(' + (row || 1) + ') .dropdown-toggle');

      return this;
    },

    /**
     * Expands filters on screen
     *
     * @return {Object} this object
     */
    expandFilter: function () {
      this.chromy.click('.chr_manage_leave_requests__filter');
      this.chromy.waitUntilVisible('.chr_manage_leave_requests__sub-header div:nth-child(1)');

      return this;
    },

    /**
     * Opens leave type filter
     *
     * @param {Number} leaveType index like 1 for Holiday/Vacation, 2 for TOIL, 3 for Sickness
     * @return {Object} this object
     */
    openLeaveTypeFor: function (leaveType) {
      this.chromy.evaluate(function (leaveType) {
        var element = document.querySelector('.chr_manage_leave_requests__header div:nth-child(1) > select');

        element.selectedIndex = leaveType;// for TOIL option
        element.dispatchEvent(new Event('change'));
      }, [leaveType]);
      this.chromy.waitUntilVisible('tbody tr:nth-child(1) a');

      return this;
    },

    /**
     * User clicks on the edit/respond action
     *
     * @param {Number} row number corresponding to leave request in the list
     * @return {Promise}
     */
    editRequest: function (row) {
      this.chromy.click('body > ul.dropdown-menu:nth-of-type(' + (row || 1) + ') li:first-child a');
      this.chromy.wait(function () {
        // = waitWhileVisible
        var dom = document.querySelector('.modal-content .spinner:nth-child(1)');
        return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
      });
      this.chromy.wait(function () {
        // = waitWhileVisible
        var dom = document.querySelector('leave-request-popup-details-tab .spinner');
        return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
      });

      return this;
    },

    /**
     * Apply leave on behalf of staff
     * @param {String} row number corresponding to leave request in the list like leave, sickness or toil
     * @return {Promise}
     */
    applyLeaveForStaff: function (leaveType) {
      var leaveSerialNo = leaveType === 'leave' ? 1 : leaveType === 'sickness' ? 2 : 3;

      this.chromy.click('.button-container leave-request-record-actions .dropdown-toggle');
      this.chromy.click('.button-container li:nth-child(' + leaveSerialNo + ') a');

      this.waitForModal('ssp-leave-request', '.chr_leave-request-modal__form');
    }
  });
})();
