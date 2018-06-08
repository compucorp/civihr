'use strict';

var Page = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');

// precondition: need to have the login of manager and have at least one sickness request
module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.openLeaveTypeFor(3);
  await page.openActionsForRow(1);
  await page.editRequest();
};
