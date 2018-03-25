const tab = require('./tab');

module.exports = tab.extend({
  readySelector: '.chr_manage_leave_requests__panel_body',
  tabUiSref: 'requests',

  /**
   * Shows filters
   */
  async showFilters () {
    await this.puppet.click('.chr_manage_leave_requests__filter');
    await this.puppet.waitFor('.chr_manage_leave_requests__sub-header', { visible: true });
  }
});
