'use strict';

var page = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');

// precondition: need to have the login of manager
module.exports = function (casper) {
  page.init(casper).applyLeaveForStaff('sickness');
};
