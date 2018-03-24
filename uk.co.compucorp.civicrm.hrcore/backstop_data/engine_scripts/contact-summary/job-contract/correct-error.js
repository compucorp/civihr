'use strict';

const pageObj = require('../../../page-objects/contact-summary');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  const tab = await page.openTab('job-contract');
  const modal = await tab.openContractModal('correct');

  await modal.selectTab('General');
};
