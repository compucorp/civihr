'use strict';

var page = require('../../../page-objects/contact-summary');

module.exports = function (engine) {
  page.init(engine).openTab('tasks');
};
