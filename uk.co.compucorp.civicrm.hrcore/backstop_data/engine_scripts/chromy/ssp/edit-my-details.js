'use strict';

var page = require('../../../page-objects/ssp/my-details');

module.exports = function (chromy) {
  page.init(chromy).showEditMyDetailsPopup();
};
