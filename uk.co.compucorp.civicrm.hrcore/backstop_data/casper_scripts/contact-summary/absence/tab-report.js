'use strict';

var absenceTab = require('../../../page-objects/tabs/absence');

module.exports = function (casper) {
  absenceTab.init(casper).openSubTab('report');
};
