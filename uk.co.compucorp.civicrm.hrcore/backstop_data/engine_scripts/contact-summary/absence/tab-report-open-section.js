'use strict';

var page = require('../../../page-objects/tabs/absence');

module.exports = function (engine) {
  page.init(engine).openSubTab('report')
    .then(function (reportTab) {
      reportTab.openSection('pending');
    });
};
