var Promise = require('es6-promise').Promise;
var tab = require('./tab');

module.exports = (function () {
  return tab.extend({
    tabTitle: 'Job Contract',

    /**
     * Clicks on the delete button
     *
     * @return {object}
     */
    attemptDelete: function () {
      var casper = this.casper;

      casper.then(function () {
        casper.click('.hrjc-list-contract-item:nth-child(1) .btn-danger');
        this.waitForModal();
      }.bind(this));
    },

    /**
     * Opens the modal of an already existing contract
     *
     * @param  {string} mode "correct" or "revision"
     * @return {Promise} resolves with the job contract modal object
     */
    openContractModal: function (mode) {
      var casper = this.casper;
      var param;

      param = mode === 'correct' ? 'edit' : (mode === 'revision' ? 'change' : '');

      return new Promise(function (resolve) {
        casper.then(function () {
          casper.click('[ng-click="modalContract(\'' + param + '\')"]');
          resolve(this.waitForModal('job-contract'));
        }.bind(this));
      }.bind(this));
    },

    /**
     * Opens the modal for creating a new contract
     *
     * @return {Promise} resolves with the job contract modal object
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
     * Overrides the original tab's `ready` method
     * There is no single selector that can be used as `readySelector` (which
     * would be used by the original `ready` method) to detect when the
     * tab is ready, so as a quick workaround we simply override the method
     * and perform all the needed checks in it
     *
     * @return {Boolean} returns `true` for the `casper.waitFor()` caller
     */
    ready: function () {
      var casper = this.casper;

      casper.waitUntilVisible('.hrjc-summary');
      casper.waitWhileVisible('.spinner');

      return casper.wait(200);
    },

    /**
     * Shows the full history of a contract
     *
     * @return {object}
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
