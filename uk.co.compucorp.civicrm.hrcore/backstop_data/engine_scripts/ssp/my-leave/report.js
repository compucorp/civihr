'use strict';

const pageObj = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have at least one pending leave request
module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.openSection('pending');
};
