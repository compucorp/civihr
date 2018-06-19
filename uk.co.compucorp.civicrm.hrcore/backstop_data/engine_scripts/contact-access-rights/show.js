'use strict';

const Page = require('../../page-objects/contact-summary');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.openManageRightsModal();
};
