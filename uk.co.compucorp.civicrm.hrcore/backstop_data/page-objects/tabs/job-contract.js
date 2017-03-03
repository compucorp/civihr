var Promise = require('es6-promise').Promise;
var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    readySelector: '.hrjc-summary',
    tabTitle: 'Job Contract',

    /**
     * [delete description]
     * @return {[type]} [description]
     */
    attemptDelete: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.hrjc-list-contract-item:nth-child(1) .btn-danger');
        this.waitForModal();
      }.bind(this));
    },

    /**
     * [openContractModal description]
     * @param  {[type]} mode [description]
     * @return {[type]}      [description]
     */
    openContractModal: function (mode) {
      var param, casper = this.casper;

      param = mode === 'correct' ? 'edit' : (mode === 'revision' ? 'change' : '');

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('[ng-click="modalContract(\'' + param + '\')"]');
          resolve(this.waitForModal('job-contract'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * [openNewContractModal description]
     * @return {[type]}      [description]
     */
    openNewContractModal: function () {
      var casper = this.casper;

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('.hrjc-btn-add-contract > .btn-primary');
          resolve(this.waitForModal('job-contract'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * [showFullHistory description]
     * @return {[type]} [description]
     */
    showFullHistory: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.clickLabel('Full History');
        casper.waitForSelector('.hrjc-context-menu-toggle');
      });
    }
  });
})();
