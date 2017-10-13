'use strict';

var page = require('../../page-objects/work-patterns-form');

module.exports = function (casper) {
  page.init(casper).showCalendarForm();
};
