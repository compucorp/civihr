'use strict';

const pageObj = require('../../../page-objects/tasks');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  const modal = await page.addTask();

  await modal.pickDate();
};
