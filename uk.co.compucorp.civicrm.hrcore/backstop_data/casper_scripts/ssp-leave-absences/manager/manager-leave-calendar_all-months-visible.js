'use strict';

var page = require('../../../page-objects/ssp-leave-absences-manager-leave-calendar');

module.exports = function (casper) {
  page.init(casper).showAllMonths();
};
