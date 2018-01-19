'use strict';

var page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

module.exports = function (casper) {
  var myReport = page.init(casper)
    .openSection('pending')
    .openActionsForRow()
    .editRequest()
    .triggerErrorAlert();
};
