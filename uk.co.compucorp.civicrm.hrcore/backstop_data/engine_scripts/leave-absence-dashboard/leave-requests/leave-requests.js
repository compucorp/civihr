'use strict';

var page = require('../../../page-objects/leave-absence-dashboard');

module.exports = function (engine) {
  page.init(engine).openTab('leave-requests');
};
