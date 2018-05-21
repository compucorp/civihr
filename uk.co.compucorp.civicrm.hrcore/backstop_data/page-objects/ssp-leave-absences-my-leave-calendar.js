const page = require('./page');

module.exports = page.extend({
  /**
   * Clears the currently selected month from the calendar "Selected Months"
   * field.
   */
  async clearCurrentlySelectedMonth () {
    await this.puppet.click('.chr_leave-calendar__day-selector .close.ui-select-match-close');
  },

  /**
   * Hovers on top of a leave day visible on the calendar until a tooltip
   * pops up.
   */
  async showTooltip () {
    await this.puppet.hover('.chr_leave-calendar__day a');
    await this.puppet.waitFor('.tooltip', { visible: true });
  },

  /**
   * Wait for the page to be ready by looking at
   * the visibility of a leave calendar item element
   */
  async waitForReady () {
    await this.puppet.waitFor('leave-calendar-month .chr_leave-calendar__day', { visible: true });
  }
});
