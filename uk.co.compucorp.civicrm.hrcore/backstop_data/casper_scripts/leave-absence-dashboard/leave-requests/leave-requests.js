'use strict';

var page = require('../../../page-objects/leave-absence-dashboard');

module.exports = function (casper) {
  page.init(casper).openTab('leave-requests');
};
