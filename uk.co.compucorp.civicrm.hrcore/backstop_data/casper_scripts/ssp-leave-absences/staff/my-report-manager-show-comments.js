'use strict';

var page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have the second leave request on the pending list with
// at least a comment from manager
module.exports = function (casper) {
  page.init(casper)
    .openSection('pending')
    .openActionsForRow(2)
    .editRequest(2).then(function (modal) {
      modal.selectTab('Comments');
    });
};
