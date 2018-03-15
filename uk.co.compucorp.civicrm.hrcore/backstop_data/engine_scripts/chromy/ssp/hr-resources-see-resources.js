'use strict';

var page = require('../../../page-objects/ssp/hr-resources');

module.exports = function (chromy) {
  page.init(chromy).seeResources();
};
