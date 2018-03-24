'use strict';

const pageObj = require('../../../page-objects/tasks');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.addTask();
};
