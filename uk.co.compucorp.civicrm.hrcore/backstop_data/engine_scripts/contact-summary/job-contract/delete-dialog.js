'use strict';

const pageObj = require('../../../page-objects/tabs/job-contract');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.attemptDelete();
};
