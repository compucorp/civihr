'use strict';

var page = require('../../page-objects/contact-actions-menu');

module.exports = function (casper) {
  page.init(casper).openContactActionMenu();
};
