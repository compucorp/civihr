'use strict';

var page = require('../../page-objects/onboarding-wizard');

module.exports = function (casper) {
  page.init(casper).reachContactInfoPage();
};
