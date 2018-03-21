'use strict';

var page = require('../../../page-objects/leave-absence-import');

module.exports = function (engine) {
  page.init(engine).showStep2();
};
