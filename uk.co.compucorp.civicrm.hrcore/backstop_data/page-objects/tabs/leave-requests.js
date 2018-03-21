var tab = require('./tab');

module.exports = tab.extend({
  readySelector: '.chr_manage_leave_requests__panel_body',
  tabUiSref: 'requests',

  /**
   * Shows filters
   */
  showFilters: function () {
    this.chromy.click('.chr_manage_leave_requests__filter');
    this.chromy.waitUntilVisible('.chr_manage_leave_requests__sub-header');
  }
});
