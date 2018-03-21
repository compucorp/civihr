var page = require('./page');

module.exports = page.extend({
  /**
   * Displays the work pattern calendar form.
   *
   * @return The Page instance.
   */
  showCalendarForm: function () {
    this.chromy.click('a[href="#work-pattern-calendar"]');

    return this;
  },

  /**
   * Waits until the work pattern form is visible.
   */
  waitForReady: function () {
    this.chromy.visible('.work-pattern-form');
  }
});
