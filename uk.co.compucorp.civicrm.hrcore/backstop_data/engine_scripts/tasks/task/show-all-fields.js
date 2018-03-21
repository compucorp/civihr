'use strict';

var page = require('../../../page-objects/tasks');

module.exports = function (engine) {
  page.init(engine).addTask().then(function (modal) {
    modal
      .showField('Subject')
      .showField('Assignee')
      .showField('Status')
      .showField('Assignment');
  });
};
