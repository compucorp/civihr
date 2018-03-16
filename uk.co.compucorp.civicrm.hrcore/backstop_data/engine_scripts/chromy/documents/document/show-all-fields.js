'use strict';

var page = require('../../../../page-objects/documents');

module.exports = function (chromy) {
  page.init(chromy).addDocument().then(function (modal) {
    modal.showTab('Assignments').showField('Assignee').showField('Assignment');
  });
};
