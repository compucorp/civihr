'use strict';

var page = require('../../page-objects/ssp/vacancies');

module.exports = function (casper) {
  page.init(casper).showMoreDetails();
};
