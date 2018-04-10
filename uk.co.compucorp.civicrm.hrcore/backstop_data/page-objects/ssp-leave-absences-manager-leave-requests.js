/* global Event */

const page = require('./page');

module.exports = page.extend({
  /**
   * Wait for the page to be ready as it waits for the actions of the first
   * row of leave requests to be visible
   */
  async waitForReady () {
    await this.puppet.waitFor('tbody tr:nth-child(1) a', { visible: true });
  },

  /**
   * Change the filter by Assignee
   *
   * @param {String} type (me|unassigned|all)
   */
  async changeFilterByAssignee (type) {
    const filters = ['me', 'unassigned', 'all'];

    await this.puppet.click(
      '.chr_manage_leave_requests__assignee_filter button:nth-of-type(' +
      (filters.indexOf(type) + 1) +
      ')');
    await this.puppet.waitFor('tbody tr:nth-child(1) a', { visible: true });
  },

  /**
   * Opens the dropdown for manager actions like edit/respond, cancel.
   *
   * @param {Number} row number corresponding to leave request in the list
   */
  async openActionsForRow (row) {
    await this.puppet.click('.chr_manage_leave_requests__panel_body tr:nth-child(' + (row || 1) + ') .dropdown-toggle');
  },

  /**
   * Expands filters on screen
   *
   */
  async expandFilter () {
    await this.puppet.click('.chr_manage_leave_requests__filter');
    await this.puppet.waitFor('.chr_manage_leave_requests__sub-header div:nth-child(1)', { visible: true });

    return this;
  },

  /**
   * Opens leave type filter
   *
   * @param {Number} leaveType index like 1 for Holiday/Vacation, 2 for TOIL, 3 for Sickness
   */
  async openLeaveTypeFor (leaveType) {
    await this.puppet.evaluate(function (leaveType) {
      const element = document.querySelector('.chr_manage_leave_requests__header div:nth-child(1) > select');

      element.selectedIndex = leaveType;
      element.dispatchEvent(new Event('change'));
    }, leaveType);
    await this.puppet.waitFor('tbody tr:nth-child(1) a', { visible: true });
  },

  /**
   * User clicks on the edit/respond action
   *
   * @param {Number} row number corresponding to leave request in the list
   */
  async editRequest (row) {
    await this.puppet.click('body > ul.dropdown-menu:nth-of-type(' + (row || 1) + ') li:first-child a');
    await this.puppet.waitFor('.modal-content .spinner:nth-child(1)', { hidden: true });
    await this.puppet.waitFor('leave-request-popup-details-tab .spinner', { hidden: true });
  },

  /**
   * Apply leave on behalf of staff
   *
   * @param {String} leaveType leave, sickness or toil
   */
  async applyLeaveForStaff (leaveType) {
    await this.puppet.click('leave-request-record-actions .dropdown-toggle');
    await this.puppet.click(`.leave-request-record-actions__new-${leaveType}`);

    await this.waitForModal('ssp-leave-request', '.chr_leave-request-modal__form');
  }
});
