'use strict';

var page = require('../../page-objects/ssp/dashboard');

module.exports = function (casper) {
  page.init(casper).showModal('[href="/absence_calendar/nojs/show"]', '.view-calendar-absence-list');
};
