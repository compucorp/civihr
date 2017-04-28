'use strict';

var page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have the second leave request on the pending list with
// at least a comment from manager
module.exports = function (casper) {
  var myReport = page.init(casper);
  myReport.openSection('\'pending\'');
  myReport.openActions(2);

  myReport.editRequest(2).then(function (modal) {
    modal.selectTab('\'Comments\'');
  });
};
