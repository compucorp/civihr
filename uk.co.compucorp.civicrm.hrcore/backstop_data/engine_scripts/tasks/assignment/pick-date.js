'use strict';

const Page = require('../../../page-objects/tasks');

module.exports = async engine => {
  const page = new Page(engine);
  await page.init();

  const modal = await page.addAssignment();
  await modal.pickDate();
};
