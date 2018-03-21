'use strict';

var page = require('../../page-objects/ssp/hr-resources');

module.exports = function (engine) {
  page.init(engine).seeResources();
};
