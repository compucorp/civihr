const page = require('./page');

module.exports = page.extend({
  /**
   * Wait for the page to be ready by looking at
   * the visibility of a leave calendar item element
   */
  async waitForReady () {
    await this.puppet.waitFor('leave-calendar-month .chr_leave-calendar__day', { visible: true });
  },

  /**
   * Toggle the calendar legend
   */
  async toggleLegend () {
    await this.puppet.click('.chr_leave-calendar__legend__title');
  },

  /**
   * Toggle contacts with leaves
   */
  async toggleContactsWithLeaves () {
    await this.puppet.click('.chr_leave-calendar__toggle-contacts-with-leaves');
  }
});
