'use strict';

var page = require('../../page-objects/onboarding-wizard');

module.exports = function (engine) {
  page.init(engine).reachDependentPage();
};
