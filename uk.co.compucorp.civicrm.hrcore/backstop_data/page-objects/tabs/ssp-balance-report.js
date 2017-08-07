var tab = require('./admin-balance-report');

module.exports = (function () {
  return tab.extend({
    tabUiSref: 'manager-leave.balance-report'
  });
})();
