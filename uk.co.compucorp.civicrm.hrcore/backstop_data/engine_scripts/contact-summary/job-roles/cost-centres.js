'use strict';

const Page = require('../../../page-objects/tabs/job-roles');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.switchToTab('Cost Centres');
};
