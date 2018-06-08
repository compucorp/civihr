'use strict';

const Page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have the absence type in *hours* with a label "Holiday in Hours"
module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.newRequest('leave');
  await page.selectRequestAbsenceType('Holiday in Hours');
};
