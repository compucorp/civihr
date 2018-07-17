'use strict';

const Page = require('../../../page-objects/tabs/job-contract');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.attemptDelete();
};
