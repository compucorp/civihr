'use strict';

var page = require('../../page-objects/ssp/tasks');

module.exports = function (casper) {
  page.init(casper).openCreateNewTaskModal();
};
