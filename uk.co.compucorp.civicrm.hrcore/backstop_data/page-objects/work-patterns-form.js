const page = require('./page');

module.exports = page.extend({
  /**
   * Displays the work pattern calendar form.
   */
  async showCalendarForm () {
    await this.puppet.click('a[href="#work-pattern-calendar"]');
  },

  /**
   * Waits until the work pattern form is visible.
   */
  async waitForReady () {
    await this.puppet.waitFor('.work-pattern-form', { visible: true });
  }
});
