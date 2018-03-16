'use strict';

var page = require('../../../../page-objects/tasks');

module.exports = function (chromy) {
  page.init(chromy).addTask().then(function (modal) {
    modal
      .showField('Subject')
      .showField('Assignee')
      .showField('Status')
      .showField('Assignment');
  });
};
