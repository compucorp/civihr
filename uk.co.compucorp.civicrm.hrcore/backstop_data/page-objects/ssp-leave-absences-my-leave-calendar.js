/* globals jQuery */

const Page = require('./page');

module.exports = class SSPLeaveAbsencesMyLeaveCalendar extends Page {
  /**
   * Clears the currently selected month from the calendar "Selected Months"
   * field.
   */
  async clearCurrentlySelectedMonth () {
    await this.puppet.click('.chr_leave-calendar__day-selector .close.ui-select-match-close');
  }

  /**
   * Displays the leave information for a particular month in the leave
   * calendar.
   *
   * @param {String} monthName - the month of the name as it appear in the
   * "Selected Months" options.
   */
  async showMonth (monthName) {
    await this.puppet.click('.chr_leave-calendar__day-selector input');
    await this.puppet.evaluate(monthName => {
      jQuery('.ui-select-choices-row:contains(' + monthName + ')').click();
    }, monthName);
    await this.puppet.waitFor('leave-calendar-month leave-calendar-day', { visible: true });
  }

  /**
   * Hovers on top of a leave day visible on the calendar until a tooltip
   * pops up.
   */
  async showTooltip () {
    await this.puppet.hover('.chr_leave-calendar__item a');
    await this.puppet.waitFor('.tooltip', { visible: true });
  }

  /**
   * Displays the leave information for a particular year in the leave calendar.
   *
   * @param {Number} year - the year to select from the absence period options.
   */
  async showYear (year) {
    await this.puppet.evaluate(year => {
      const select = jQuery('.chr_manager_calendar__sub-header select');
      const yearValue = select.find('option:contains(' + year + ')').attr('value');

      select.val(yearValue).change();
    }, year);
    await this.puppet.waitFor('leave-calendar-month leave-calendar-day', { visible: true });
  }

  /**
   * Wait for the page to be ready by looking at
   * the visibility of a leave calendar item element
   */
  async waitForReady () {
    await this.puppet.waitFor('leave-calendar-month .chr_leave-calendar__item', { visible: true });
  }
};
