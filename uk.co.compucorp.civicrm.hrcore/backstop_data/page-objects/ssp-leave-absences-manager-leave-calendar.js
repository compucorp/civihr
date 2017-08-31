var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Wait for the page to be ready by looking at
     * the visibility of a leave calendar item element
     */
    waitForReady: function () {
      this.waitUntilVisible('leave-calendar-month .chr_leave-calendar__item');
    },

    /**
     * Toggle the calendar legend
     *
     * @return {Promise}
     */
    toggleLegend: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.chr_leave-calendar__legend__title');
      });

      return this;
    },

    /**
     * Toggle contacts with leaves
     *
     * @return {Promise}
     */
    toggleContactsWithLeaves: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.chr_leave-calendar__toggle-contacts-with-leaves');
      });

      return this;
    }
  });
})();
