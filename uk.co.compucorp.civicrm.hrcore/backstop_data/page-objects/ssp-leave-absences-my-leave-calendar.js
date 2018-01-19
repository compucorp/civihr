/* globals jQuery */

var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Clears the currently selected month from the calendar "Selected Months"
     * field.
     *
     * @returns {Object} - returns a reference to itself.
     */
    clearCurrentlySelectedMonth: function () {
      this.casper.click('.chr_leave-calendar__day-selector .close.ui-select-match-close');

      return this;
    },

    /**
     * Displays the leave information for a particular month in the leave
     * calendar.
     *
     * @param {String} monthName - the month of the name as it appear in the
     * "Selected Months" options.
     * @returns {Object} - returns a reference to itself.
     */
    showMonth: function (monthName) {
      this.casper.click('.chr_leave-calendar__day-selector input');
      this.casper.evaluate(function (monthName) {
        jQuery('.ui-select-choices-row:contains(' + monthName + ')').click();
      }, monthName);
      this.waitUntilVisible('leave-calendar-month leave-calendar-day');

      return this;
    },

    /**
     * Hovers on top of a leave day visible on the calendar until a tooltip
     * pops up.
     *
     * @returns {Object} - returns a reference to itself.
     */
    showTooltip: function () {
      this.casper.then(function () {
        this.mouse.move('.chr_leave-calendar__item a');
      });
      this.waitUntilVisible('.tooltip');

      return this;
    },

    /**
     * Displays the leave information for a particular year in the leave calendar.
     *
     * @param {Number} year - the year to select from the absence period options.
     * @returns {Object} - returns a reference to itself.
     */
    showYear: function (year) {
      this.casper.evaluate(function (year) {
        var select = jQuery('.chr_manager_calendar__sub-header select');
        var yearValue = select.find('option:contains(' + year + ')').attr('value');

        select.val(yearValue).change();
      }, year);
      this.waitUntilVisible('leave-calendar-month leave-calendar-day');

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
