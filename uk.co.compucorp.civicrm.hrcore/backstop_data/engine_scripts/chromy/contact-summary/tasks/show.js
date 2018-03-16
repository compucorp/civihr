'use strict';

var page = require('../../../../page-objects/contact-summary');

module.exports = function (chromy) {
  page.init(chromy).openTab('tasks');
};
