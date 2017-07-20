'use strict';

var page = require('../../page-objects/ssp/dashboard');

module.exports = function (casper) {
  page.init(casper).showModal('[href="/absence_request/nojs/credit"]', '.modal-civihr-custom__section');
};
