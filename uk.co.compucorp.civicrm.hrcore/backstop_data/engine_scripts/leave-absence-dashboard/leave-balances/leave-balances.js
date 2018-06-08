'use strict';

const Page = require('../../../page-objects/leave-absence-dashboard');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.openTab('leave-balances');
};
