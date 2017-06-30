var _ = require('lodash');
var tab = require('../tabs/tab');
var sspMyLeaveCalendar = require('../ssp-leave-absences-my-leave-calendar');

module.exports = (function () {
  return tab.extend({
    readySelector: '.chr_leave-calendar__month-body',
    tabTitle: 'Calendar'
  })
  .extend(_.omit(sspMyLeaveCalendar, 'waitForReady'));
})();
