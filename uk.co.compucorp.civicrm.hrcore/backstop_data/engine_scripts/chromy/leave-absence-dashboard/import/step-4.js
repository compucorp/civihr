'use strict';

var page = require('../../../../page-objects/leave-absence-import');

module.exports = function (chromy) {
  page.init(chromy).showStep4();
};
