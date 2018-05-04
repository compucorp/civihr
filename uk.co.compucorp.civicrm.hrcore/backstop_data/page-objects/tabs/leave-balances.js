const tab = require('./tab');

module.exports = tab.extend({
  readySelector: '.chr_leave-balance-tab__body .table-responsive',
  tabTitle: 'Leave Balance',
  tabUiSref: 'leave-balances'
});
