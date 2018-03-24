'use strict';

const pageObj = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');

// precondition: need to have the login of manager
module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.applyLeaveForStaff('toil');
};
