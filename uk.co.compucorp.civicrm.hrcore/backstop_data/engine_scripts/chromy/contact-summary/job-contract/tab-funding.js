'use strict';

var page = require('../../../../page-objects/contact-summary');

module.exports = function (chromy) {
  page.init(chromy).openTab('job-contract')
    .then(function (tab) {
      return tab.openNewContractModal();
    })
    .then(function (modal) {
      modal.selectTab('Funding');
    });
};
