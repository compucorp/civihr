'use strict';

var page = require('../../page-objects/admin-leave-absences-leave-calendar');

module.exports = function (casper) {
  page.init(casper).showAllMonths();
};
