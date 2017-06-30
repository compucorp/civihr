'use strict';

var page = require('../../../page-objects/absence-tabs/calendar');

module.exports = function (casper) {
  page.init(casper).showAllMonths();
};
