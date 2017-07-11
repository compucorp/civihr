var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: '.chr_manage_leave_requests__panel_body',
    tabUiSref: 'requests',

    /**
     * Shows filters
     */
    showFilters: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.chr_manage_leave_requests__filter');
        casper.waitUntilVisible('.chr_manage_leave_requests__sub-header');
      });
    }
  });
})();
