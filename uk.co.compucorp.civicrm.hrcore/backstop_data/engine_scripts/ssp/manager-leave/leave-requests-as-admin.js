'use strict';

const pageObj = require('../../../page-objects/ssp-leave-absences-manager-leave-requests');

// precondition: need to have the login of Admin
// and have at least one leave request *assigned* to the Admin
module.exports = async engine => {
  await pageObj.init(engine);
};
