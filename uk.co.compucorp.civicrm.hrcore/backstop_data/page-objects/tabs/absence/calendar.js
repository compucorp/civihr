var tab = require('../tab');
var sspMyLeaveCalendar = require('../../ssp-leave-absences-my-leave-calendar');

module.exports = sspMyLeaveCalendar.extend(tab).extend({
  readySelector: '.chr_leave-calendar__month-body',
  tabTitle: 'Calendar',
  waitForReady: null
});
