'use strict';

var page = require('../../../page-objects/tabs/absence/entitlements');

module.exports = function (casper) {
  page.init(casper).waitForReady();
};
