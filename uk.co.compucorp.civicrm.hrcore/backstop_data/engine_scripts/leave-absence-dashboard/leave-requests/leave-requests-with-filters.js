'use strict';

const pageObj = require('../../../page-objects/leave-absence-dashboard');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  const tab = await page.openTab('leave-requests');

  await tab.showFilters();
};
