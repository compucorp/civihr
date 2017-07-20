'use strict';

var page = require('../../page-objects/ssp/dashboard');

module.exports = function (casper) {
  page.init(casper).openDatePicker();
};
