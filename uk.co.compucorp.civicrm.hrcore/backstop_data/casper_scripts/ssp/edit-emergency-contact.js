'use strict';

var page = require('../../page-objects/ssp/my-details');

module.exports = function (casper) {
  page.init(casper).showEditEmergencyContactPopup();
};
