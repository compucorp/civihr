'use strict';

const pageObj = require('../../page-objects/ssp-hr-resources');

module.exports = async engine => {
  const page = await pageObj.init(engine);

  await page.seeResources();
};
