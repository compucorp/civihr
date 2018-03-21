'use strict';

var page = require('../../page-objects/ssp/vacancies');

module.exports = function (engine) {
  page.init(engine).showMoreDetails();
};
