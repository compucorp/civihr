'use strict';

const Page = require('../../../page-objects/ssp-leave-absences-my-leave-calendar');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
  await page.showTooltip();
};
