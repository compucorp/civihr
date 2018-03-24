'use strict';

const pageObj = require('../../../page-objects/ssp-leave-absences-my-leave-report');

// precondition: need to have the absence type in *hours* with a label "Holiday in Hours"
module.exports = async engine => {
  const page = await pageObj.init(engine);

  await page.newRequest('leave');
  await page.selectRequestAbsenceType('Holiday in Hours');
};
