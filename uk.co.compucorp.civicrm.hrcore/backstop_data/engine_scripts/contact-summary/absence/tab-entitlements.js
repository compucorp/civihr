'use strict';

const Page = require('../../../page-objects/tabs/absence');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.openSubTab('entitlements');
};
