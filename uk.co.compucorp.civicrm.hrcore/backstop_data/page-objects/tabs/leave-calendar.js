var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: 'leave-calendar-day',
    tabTitle: 'Leave Calendar',
    tabUiSref: 'calendar'
  });
})();
