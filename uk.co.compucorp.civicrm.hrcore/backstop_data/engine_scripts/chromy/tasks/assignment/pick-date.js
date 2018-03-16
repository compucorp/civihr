'use strict';

var page = require('../../../../page-objects/tasks');

module.exports = function (chromy) {
  page.init(chromy).addAssignment().then(function (modal) {
    modal.pickDate();
  });
};
