'use strict';

const pageObj = require('../../../page-objects/tabs/job-roles');

module.exports = async engine => {
  const page = await pageObj.init(engine);

  await page.showAddNew();
};
