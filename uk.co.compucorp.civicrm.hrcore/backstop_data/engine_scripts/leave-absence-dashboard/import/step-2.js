'use strict';

const pageObj = require('../../../page-objects/leave-absence-import');

module.exports = async engine => {
  const page = await pageObj.init(engine);

  await page.showStep2();
};
