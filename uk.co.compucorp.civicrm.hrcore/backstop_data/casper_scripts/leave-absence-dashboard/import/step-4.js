'use strict';

var page = require('../../../page-objects/leave-absence-import');

module.exports = function (casper) {
  page.init(casper).showStep4();
};
