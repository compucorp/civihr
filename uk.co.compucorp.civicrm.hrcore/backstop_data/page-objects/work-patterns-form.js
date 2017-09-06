var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Displays the work pattern calendar form.
     *
     * @return The Page instance.
     */
    showCalendarForm: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('a[href="#work-pattern-calendar"]');
      });

      return this;
    },

    /**
     * Waits until the work pattern form is visible.
     */
    waitForReady: function () {
      this.waitUntilVisible('.work-pattern-form');
    }
  });
})();
