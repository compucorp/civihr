'use strict';

var page = require('../../../page-objects/ssp-leave-absences');

module.exports = function (casper) {
  var myReport = page.init(casper).openMyReport();
  myReport.openMyReportSection('\'pending\'');
  myReport.openActionsMyReport();

  myReport.editRequestForMyReport().then(function (modal) {
    modal.selectTab('\'Comments\'');
    modal.addCommentToScope('Sample Comment');
    modal.addComment();
  });
};
