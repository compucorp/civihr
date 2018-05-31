'use strict';

const Page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have at least one pending leave request
module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.openSection('pending');
};
