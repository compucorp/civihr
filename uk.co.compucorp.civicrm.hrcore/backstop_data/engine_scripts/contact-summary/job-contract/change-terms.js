'use strict';

const pageObj = require('../../../page-objects/tabs/job-contract');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  const modal = await page.openContractModal('revision');

  await modal.selectTab('General');
};
