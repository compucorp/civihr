'use strict';

const Page = require('../../page-objects/documents');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.advancedFilters();
};
