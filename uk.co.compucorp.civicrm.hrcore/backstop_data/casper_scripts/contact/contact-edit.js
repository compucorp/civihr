'use strict';

var page = require('../../page-objects/edit-form');

module.exports = function (casper) {
  page.init(casper).editForm();
};
