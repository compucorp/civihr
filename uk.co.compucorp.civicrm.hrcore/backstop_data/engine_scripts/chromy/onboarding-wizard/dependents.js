'use strict';

var page = require('../../../page-objects/onboarding-wizard');

module.exports = function (chromy) {
  page.init(chromy).reachDependentPage();
};
