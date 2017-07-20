'use strict';

var page = require('../../page-objects/ssp/hr-resources');

module.exports = function (casper) {
  page.init(casper).seeResources();
};
