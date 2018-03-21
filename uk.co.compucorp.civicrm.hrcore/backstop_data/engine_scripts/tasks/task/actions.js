'use strict';

var page = require('../../../page-objects/tasks');

module.exports = function (engine) {
  page.init(engine).taskActions();
};
