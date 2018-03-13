'use strict';

var page = require('../../../page-objects/work-patterns-form');

module.exports = function (chromy) {
  page.init(chromy).showCalendarForm();
};
