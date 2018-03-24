'use strict';

const pageObj = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');

// precondition: need to have the login of manager and have at least one toil request
module.exports = async engine => {
  const page = await pageObj.init(engine);

  await page.openLeaveTypeFor(2);
  await page.openActionsForRow(1);
  await page.editRequest();
};
