'use strict';

var page = require('../../../../page-objects/tasks');

module.exports = function (chromy) {
  page.init(chromy).inPlaceEdit('subject');
};
