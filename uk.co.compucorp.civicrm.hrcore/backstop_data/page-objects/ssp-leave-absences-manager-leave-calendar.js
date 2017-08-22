var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Wait for the page to be ready by looking at
     * the visibility of a leave calendar item element
     */
    waitForReady: function () {
      this.waitUntilVisible('leave-calendar-month .chr_leave-calendar__item');
    }
  });
})();
