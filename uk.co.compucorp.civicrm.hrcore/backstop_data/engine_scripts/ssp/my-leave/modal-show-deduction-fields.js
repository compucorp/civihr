'use strict';

const Page = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have a current absence period
module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.newRequest('leave');
  await page.selectRequestAbsenceType('Holiday in Hours');
  await page.changeRequestDaysMode('multiple');
  await page.selectRequestDate('from', 2, 1);
  await page.selectRequestDate('to', 2, 2);
  await page.waitUntilRequestBalanceIsCalculated();
};
