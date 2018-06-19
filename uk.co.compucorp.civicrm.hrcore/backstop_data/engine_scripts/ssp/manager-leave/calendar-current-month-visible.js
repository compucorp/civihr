'use strict';

const Page = require('../../../page-objects/ssp-leave-absences-manager-leave-calendar');

module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
};
