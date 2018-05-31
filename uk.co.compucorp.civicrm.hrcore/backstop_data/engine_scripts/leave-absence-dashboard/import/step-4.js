'use strict';

const Page = require('../../../page-objects/leave-absence-import');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.showStep4();
};
