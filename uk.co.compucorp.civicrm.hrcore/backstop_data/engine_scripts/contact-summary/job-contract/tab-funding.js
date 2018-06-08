'use strict';

const Page = require('../../../page-objects/tabs/job-contract');

module.exports = async engine => {
  const page = new Page(engine);
  await page.init();

  const modal = await page.openNewContractModal();
  await modal.selectTab('Funding');
};
