'use strict';

var page = require('../../page-objects/work-patterns-form');

module.exports = function (engine) {
  page.init(engine).showCalendarForm();
};
