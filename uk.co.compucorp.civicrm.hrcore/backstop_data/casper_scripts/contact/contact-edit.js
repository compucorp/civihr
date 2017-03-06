'use strict';

var page = require('../../page-objects/contact-edit-form');

module.exports = function (casper) {
  page.init(casper).editForm();
};
