'use strict';

var page = require('../../../page-objects/absence-tabs/entitlements');

module.exports = function (casper) {
  page.init(casper).waitForReady();
};
