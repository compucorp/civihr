'use strict';

const pageObj = require('../../page-objects/contact-summary');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  const modal = await page.openManageRightsModal();

  await modal.openDropdown('locations');
};
