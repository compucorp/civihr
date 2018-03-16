'use strict';

var page = require('../../../../page-objects/contact-summary');

module.exports = function (chromy) {
  page.init(chromy).openTab('job-roles')
    .then(function (tab) {
      tab.switchToTab('Basic Details').edit().openDropdown('department');
    });
};
