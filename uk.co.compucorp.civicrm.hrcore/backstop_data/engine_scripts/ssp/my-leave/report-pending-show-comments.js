'use strict';

const pageObj = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have the first leave request on the pending list with at least a comment
module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.openSection('pending');
  await page.openActionsForRow();

  const modal = await page.editRequest();
  await modal.selectTab('Comments');
};
