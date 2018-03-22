'use strict';

var page = require('../../page-objects/ssp-my-details');

module.exports = function (engine) {
  page.init(engine).showEditMyDetailsPopup();
};
