'use strict';

var page = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');

// precondition: need to have the login of manager
module.exports = function (engine) {
  page.init(engine).applyLeaveForStaff('toil');
};
