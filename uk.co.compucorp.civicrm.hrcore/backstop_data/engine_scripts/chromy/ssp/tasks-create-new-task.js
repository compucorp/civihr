'use strict';

var page = require('../../../page-objects/ssp/tasks');

module.exports = function (chromy) {
  page.init(chromy).openCreateNewTaskModal();
};
