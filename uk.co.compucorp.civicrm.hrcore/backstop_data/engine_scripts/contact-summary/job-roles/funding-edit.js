'use strict';

var page = require('../../../page-objects/tabs/job-roles');

module.exports = function (engine) {
  page.init(engine).switchToTab('Funding').edit();
};
