'use strict';

const pageObj = require('../../../page-objects/tabs/absence');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  const tab = await page.openSubTab('report');

  await tab.openSection('pending');
};
