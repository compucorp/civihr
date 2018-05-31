'use strict';

const Page = require('../../../page-objects/tabs/absence');

module.exports = async engine => {
  const page = new Page(engine);
  await page.init();

  const tab = await page.openSubTab('work-patterns');
  await tab.showModal();
};
