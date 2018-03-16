'use strict';

var page = require('../../../page-objects/documents');

module.exports = function (chromy) {
  page.init(chromy).advancedFilters();
};
