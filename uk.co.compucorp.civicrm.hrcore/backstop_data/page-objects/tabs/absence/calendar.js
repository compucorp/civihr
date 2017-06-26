var sspMyLeaveCalendar = require('./../../ssp-leave-absences-my-leave-calendar');

module.exports = (function () {
  return sspMyLeaveCalendar.extend({
    /**
     * Wait for the page to be ready
     */
    waitForReady: function () {
      var casper = this.casper;
      casper.click('[data-tabname="calendar"] > a');
      sspMyLeaveCalendar.waitForReady.call(this);
    }
  });
})();
