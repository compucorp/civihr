'use strict';

const pageObj = require('../../../page-objects/tabs/absence');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.openSubTab('calendar');
};
