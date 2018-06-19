const SSPMyLeaveCalendar = require('../../ssp-leave-absences-my-leave-calendar');

module.exports = class AbsenceCalendarTab extends SSPMyLeaveCalendar {
  constructor () {
    super(...arguments);

    this.readySelector = '.chr_leave-calendar__month-body';
    this.tabTitle = 'Calendar';
  }
};
