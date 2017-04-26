'use strict';

var page = require('../../../page-objects/ssp-leave-absences');
module.exports = function (casper) {
  page.init(casper).initPage()
    .then(function () {
      page.init(casper).allMonths();
    });
};
