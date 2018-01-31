'use strict';

var page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have a current absence period
module.exports = function (casper) {
  page.init(casper)
    .newRequest('leave')
    .selectRequestAbsenceType('Holiday in Hours')
    .changeRequestDaysMode('multiple')
    .selectRequestDate('from', 2, 1)
    .selectRequestDate('to', 2, 2)
    .waitUntilRequestBalanceIsCalculated()
    .expandDeductionField('from')
    .expandDeductionField('to');
};
