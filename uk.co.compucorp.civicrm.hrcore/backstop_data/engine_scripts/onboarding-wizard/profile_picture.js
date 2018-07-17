'use strict';

const Page = require('../../page-objects/onboarding-wizard');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.reachProfilePicturePage();
};
