'use strict';

const pageObj = require('../../page-objects/ssp-my-details');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.showEditMyDetailsPopup();
};
