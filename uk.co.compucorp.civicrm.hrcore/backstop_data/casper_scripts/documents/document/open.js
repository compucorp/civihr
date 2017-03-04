'use strict';

var page = require('../../../page-objects/documents');

module.exports = function (casper) {
  page.init(casper).openDocument();
};
