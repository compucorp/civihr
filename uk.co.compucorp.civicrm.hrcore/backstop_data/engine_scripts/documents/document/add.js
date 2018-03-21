'use strict';

var page = require('../../../page-objects/documents');

module.exports = function (engine) {
  page.init(engine).addDocument();
};
