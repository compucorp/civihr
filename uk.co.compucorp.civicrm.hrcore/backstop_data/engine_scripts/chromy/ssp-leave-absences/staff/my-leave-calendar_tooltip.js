'use strict';

var page = require('../../../../page-objects/ssp-leave-absences-my-leave-calendar');

module.exports = function (chromy) {
  page.init(chromy).showTooltip();
};
