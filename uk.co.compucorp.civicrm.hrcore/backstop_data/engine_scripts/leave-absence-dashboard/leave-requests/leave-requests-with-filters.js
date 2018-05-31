'use strict';

const Page = require('../../../page-objects/leave-absence-dashboard');

module.exports = async engine => {
  const page = new Page(engine);
  await page.init();

  const tab = await page.openTab('leave-requests');
  await tab.showFilters();
};
