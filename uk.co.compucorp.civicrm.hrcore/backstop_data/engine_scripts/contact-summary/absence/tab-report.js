'use strict';

var absenceTab = require('../../../page-objects/tabs/absence');

module.exports = function (engine) {
  absenceTab.init(engine).openSubTab('report');
};
