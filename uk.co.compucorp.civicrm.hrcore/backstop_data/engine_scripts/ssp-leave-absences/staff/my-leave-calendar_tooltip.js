'use strict';

var page = require('../../../page-objects/ssp-leave-absences-my-leave-calendar');

module.exports = function (engine) {
  page.init(engine).showTooltip();
};
