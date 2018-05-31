const Tab = require('./tab');

module.exports = class LeaveCalendarTab extends Tab {
  constructor () {
    super(...arguments);
    this.readySelector = 'leave-calendar-day';
    this.tabTitle = 'Leave Calendar';
    this.tabUiSref = 'calendar';
  }
};
