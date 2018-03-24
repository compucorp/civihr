'use strict';

const pageObj = require('../../page-objects/onboarding-wizard');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.reachAddressPage();
};
