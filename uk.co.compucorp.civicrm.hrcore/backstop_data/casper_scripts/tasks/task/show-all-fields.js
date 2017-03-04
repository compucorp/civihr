'use strict';

var page = require('../../../page-objects/tasks');

module.exports = function (casper) {
  page.init(casper).addTask().then(function (modal) {
    modal
      .showField('Subject')
      .showField('Assignee')
      .showField('Status')
      .showField('Assignment');
  });
};
