/* globals jQuery */

var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Clears all selected months from the calendar "Selected Months" field.
     */
    clearAllSelectedMonths: function () {
      this.casper.evaluate(function () {
        jQuery('.chr_leave-calendar__day-selector .close.ui-select-match-close')
          .each(function () {
            jQuery(this).click();
          });
      });

      return this;
    },

    /**
     * Display all months for the leave calendar by clearing the
     * "Selected Months" field.
     */
    showAllMonths: function () {
      this.clearAllSelectedMonths();
      this.waitUntilVisible('leave-calendar-month:nth-child(12) leave-calendar-day');

      return this;
    },

    /**
     * Wait for the page to be ready by looking at
     * the visibility of a leave calendar item element
     */
    waitForReady: function () {
      this.waitUntilVisible('leave-calendar-month .chr_leave-calendar__item');
    }
  });
})();
