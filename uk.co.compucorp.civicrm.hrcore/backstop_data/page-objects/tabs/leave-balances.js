const Tab = require('./tab');

module.exports = class LeaveBalancesTab extends Tab {
  constructor () {
    super(...arguments);

    this.readySelector = '.chr_leave-balance-tab__body .table-responsive';
    this.tabTitle = 'Leave Balance';
    this.tabUiSref = 'leave-balances';
  }
};
