'use strict';

const pageObj = require('../../page-objects/documents');

module.exports = async engine => {
  const page = await pageObj.init(engine);

  await page.advancedFilters();
};
