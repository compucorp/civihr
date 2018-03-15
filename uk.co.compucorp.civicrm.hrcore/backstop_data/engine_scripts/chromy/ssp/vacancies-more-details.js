'use strict';

var page = require('../../../page-objects/ssp/vacancies');

module.exports = function (chromy) {
  page.init(chromy).showMoreDetails();
};
