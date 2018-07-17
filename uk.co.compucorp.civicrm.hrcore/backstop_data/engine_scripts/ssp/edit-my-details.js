'use strict';

const Page = require('../../page-objects/ssp-my-details');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.showEditMyDetailsPopup();
};
