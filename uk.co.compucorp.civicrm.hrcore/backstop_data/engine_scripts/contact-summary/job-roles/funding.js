'use strict';

const pageObj = require('../../../page-objects/contact-summary');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  const tab = await page.openTab('job-roles');

  await tab.switchToTab('Funding');
};
