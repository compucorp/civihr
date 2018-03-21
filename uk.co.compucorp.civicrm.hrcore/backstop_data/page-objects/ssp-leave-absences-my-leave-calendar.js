/* globals jQuery, MouseEvent */

var page = require('./page');

module.exports = page.extend({
  /**
   * Clears the currently selected month from the calendar "Selected Months"
   * field.
   *
   * @returns {Object} - returns a reference to the page object.
   */
  clearCurrentlySelectedMonth: function () {
    this.chromy.click('.chr_leave-calendar__day-selector .close.ui-select-match-close');

    return this;
  },

  /**
   * Displays the leave information for a particular month in the leave
   * calendar.
   *
   * @param {String} monthName - the month of the name as it appear in the
   * "Selected Months" options.
   * @returns {Object} - returns a reference to the page object.
   */
  showMonth: function (monthName) {
    this.chromy.click('.chr_leave-calendar__day-selector input');
    this.chromy.evaluate(function (monthName) {
      jQuery('.ui-select-choices-row:contains(' + monthName + ')').click();
    }, [monthName]);
    this.chromy.waitUntilVisible('leave-calendar-month leave-calendar-day');

    return this;
  },

  /**
   * Hovers on top of a leave day visible on the calendar until a tooltip
   * pops up.
   *
   * @returns {Object} - returns a reference to the page object.
   */
  showTooltip: function () {
    var chromy = this.chromy;

    chromy.evaluate(function () {
      var event = new MouseEvent('mouseover');
      document.querySelector('.chr_leave-calendar__item a').dispatchEvent(event);
    });
    chromy.waitUntilVisible('.tooltip');

    return this;
  },

  /**
   * Displays the leave information for a particular year in the leave calendar.
   *
   * @param {Number} year - the year to select from the absence period options.
   * @returns {Object} - returns a reference to the page object.
   */
  showYear: function (year) {
    this.chromy.evaluate(function (year) {
      var select = jQuery('.chr_manager_calendar__sub-header select');
      var yearValue = select.find('option:contains(' + year + ')').attr('value');

      select.val(yearValue).change();
    }, [year]);
    this.chromy.waitUntilVisible('leave-calendar-month leave-calendar-day');

    return this;
  },

  /**
   * Wait for the page to be ready by looking at
   * the visibility of a leave calendar item element
   */
  waitForReady: function () {
    this.chromy.waitUntilVisible('leave-calendar-month .chr_leave-calendar__item');
  }
});
