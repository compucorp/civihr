'use strict';

var page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have the first leave request on the pending list of leave type toil
module.exports = function (casper) {
  var myReport = page.init(casper)
    .openSection('pending')
    .openActionsForRow()
    .editRequest();
};
