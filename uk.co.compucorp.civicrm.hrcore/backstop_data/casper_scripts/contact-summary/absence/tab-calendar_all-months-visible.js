'use strict';

var page = require('../../../page-objects/tabs/absence/calendar');

module.exports = function (casper) {
  page.init(casper).showAllMonths();
};
