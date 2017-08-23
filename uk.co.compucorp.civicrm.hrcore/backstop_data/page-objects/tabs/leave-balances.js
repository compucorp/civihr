var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: '.chr_leave-balance-tab',
    tabTitle: 'Leave Balance',
    tabUiSref: 'leave-balances'
  });
})();
