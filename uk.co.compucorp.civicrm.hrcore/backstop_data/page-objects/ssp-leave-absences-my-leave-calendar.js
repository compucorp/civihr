/* globals jQuery */

var page = require('./page');

module.exports = (function () {
  return page.extend({
    /**
     * Clears all selected months from the calendar "Selected Months" field.
     *
     * @returns {Object} - returns a reference to itself.
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
     *
     * @returns {Object} - returns a reference to itself.
     */
    showAllMonths: function () {
      this.clearAllSelectedMonths();
      this.waitUntilVisible('leave-calendar-month:nth-child(12) leave-calendar-day');

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
