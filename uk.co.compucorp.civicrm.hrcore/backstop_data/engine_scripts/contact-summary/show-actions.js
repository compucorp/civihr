'use strict';

const pageObj = require('../../page-objects/contact-summary');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.showActions();
};
