'use strict';

const pageObj = require('../../page-objects/ssp-tasks');

module.exports = async engine => {
  const page = await pageObj.init(engine);

  await page.openCreateNewTaskModal();
};
