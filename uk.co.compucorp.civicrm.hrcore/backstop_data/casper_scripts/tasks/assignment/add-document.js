'use strict';

var page = require('../../../page-objects/tasks');

module.exports = function (casper) {
  page.init(casper).addAssignment().then(function (modal) {
    modal.selectType().addDocument();
  });
};
