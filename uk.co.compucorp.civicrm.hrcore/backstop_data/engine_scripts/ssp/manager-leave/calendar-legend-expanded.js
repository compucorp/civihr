'use strict';

const pageObj = require('../../../page-objects/ssp-leave-absences-manager-leave-calendar');

module.exports = async engine => {
  const page = await pageObj.init(engine);
  await page.toggleLegend();
};
