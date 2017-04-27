'use strict';

var page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have the first leave request on the pending list with at least a comment
module.exports = function (casper) {
  var myReport = page.init(casper);
  myReport.openSection('\'pending\'');
  myReport.openActions();

  myReport.editRequest().then(function (modal) {
    modal.selectTab('\'Comments\'');
  });
};
