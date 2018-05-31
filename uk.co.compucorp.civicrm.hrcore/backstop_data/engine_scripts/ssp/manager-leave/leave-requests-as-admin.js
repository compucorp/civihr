'use strict';

const Page = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');

// precondition: need to have the login of Admin
// and have at least one leave request *assigned* to the Admin
module.exports = async engine => {
  const page = new Page(engine);

  await page.init();
};
