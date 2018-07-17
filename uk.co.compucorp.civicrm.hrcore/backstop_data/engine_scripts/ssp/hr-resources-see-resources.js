'use strict';

const Page = require('../../page-objects/ssp-hr-resources');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.seeResources();
};
