const Tab = require('./tab');

module.exports = class LeaveRequestsTab extends Tab {
  constructor () {
    super(...arguments);

    this.readySelector = '.chr_manage_leave_requests__panel_body';
    this.tabUiSref = 'requests';
  }

  /**
   * Shows filters
   */
  async showFilters () {
    await this.puppet.click('.chr_manage_leave_requests__filter');
    await this.puppet.waitFor('.chr_manage_leave_requests__sub-header', { visible: true });
  }
};
