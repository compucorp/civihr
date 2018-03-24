'use strict';

const pageObj = require('../../page-objects/work-patterns-form');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.showCalendarForm();
};
