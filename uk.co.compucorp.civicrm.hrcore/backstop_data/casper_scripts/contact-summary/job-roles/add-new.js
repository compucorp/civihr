'use strict';

var page = require('../../../page-objects/contact-summary');

module.exports = function (casper) {
  page.init(casper).openTab('job-roles')
    .then(function (tab) {
      tab.showAddNew();
    });
};
