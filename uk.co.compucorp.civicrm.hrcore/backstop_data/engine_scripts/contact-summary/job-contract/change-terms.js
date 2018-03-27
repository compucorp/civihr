'use strict';

var page = require('../../../page-objects/tabs/job-contract');

module.exports = function (engine) {
  page.init(engine).openContractModal('revision')
    .then(function (modal) {
      modal.selectTab('General');
    });
};
