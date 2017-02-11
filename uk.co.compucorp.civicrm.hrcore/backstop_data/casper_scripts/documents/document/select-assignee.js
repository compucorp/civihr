'use strict';

var page = require('../../../page-objects/documents');

module.exports = function (casper) {
  page.init(casper).addDocument().then(function (modal) {
    modal.showField('Assignee').selectAssignee();
  });
};
