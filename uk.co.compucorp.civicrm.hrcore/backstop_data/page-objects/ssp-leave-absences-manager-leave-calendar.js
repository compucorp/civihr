var page = require('./page');

module.exports = page.extend({
  /**
   * Wait for the page to be ready by looking at
   * the visibility of a leave calendar item element
   */
  waitForReady: function () {
    this.chromy.waitUntilVisible('leave-calendar-month .chr_leave-calendar__item');
  },

  /**
   * Toggle the calendar legend
   *
   * @return {Promise}
   */
  toggleLegend: function () {
    this.chromy.click('.chr_leave-calendar__legend__title');

    return this;
  },

  /**
   * Toggle contacts with leaves
   *
   * @return {Promise}
   */
  toggleContactsWithLeaves: function () {
    this.chromy.click('.chr_leave-calendar__toggle-contacts-with-leaves');

    return this;
  }
});
