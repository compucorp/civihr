'use strict';

var absenceTab = require('../../../../page-objects/tabs/absence');

module.exports = function (chromy) {
  absenceTab.init(chromy).openSubTab('report')
    .then(function (reportTab) {
      reportTab.openSection('pending');
    });
};
