const tab = require('../tab');
const sspMyLeaveCalendar = require('../../ssp-leave-absences-my-leave-calendar');

module.exports = sspMyLeaveCalendar.extend(tab).extend({
  readySelector: '.chr_leave-calendar__month-body',
  tabTitle: 'Calendar'
});
