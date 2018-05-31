'use strict';

const Page = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');

// precondition: need to have the login of manager and have at least one toil request
module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.openLeaveTypeFor(2);
  await page.openActionsForRow(1);
  await page.editRequest();
};
