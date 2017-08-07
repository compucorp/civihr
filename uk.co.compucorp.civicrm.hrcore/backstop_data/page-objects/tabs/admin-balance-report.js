var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: '.chr_leave-balance-report',
    tabTitle: 'Leave Balance',
    tabUiSref: 'balance-report'
  });
})();
