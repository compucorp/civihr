'use strict';

var page = require('../../page-objects/contact-summary');

module.exports = function (casper) {
  page.init(casper).openManageRightsModal();
};
