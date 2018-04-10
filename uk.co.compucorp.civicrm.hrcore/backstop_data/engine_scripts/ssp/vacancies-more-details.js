'use strict';

const pageObj = require('../../page-objects/ssp-vacancies');

module.exports = async engine => {
  const page = await pageObj.init(engine);

  await page.showMoreDetails();
};
