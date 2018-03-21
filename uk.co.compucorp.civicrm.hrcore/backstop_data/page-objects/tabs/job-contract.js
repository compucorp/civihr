var Promise = require('es6-promise').Promise;
var tab = require('./tab');

module.exports = tab.extend({
  tabTitle: 'Job Contract',

  /**
   * Clicks on the delete button
   *
   * @return {object}
   */
  attemptDelete: function () {
    this.chromy.click('.hrjc-list-contract-item:nth-child(1) .btn-danger');
    this.waitForModal();
  },

  /**
   * Opens the modal of an already existing contract
   *
   * @param  {string} mode "correct" or "revision"
   * @return {Promise} resolves with the job contract modal object
   */
  openContractModal: function (mode) {
    var param = mode === 'correct' ? 'edit' : (mode === 'revision' ? 'change' : '');

    return new Promise(function (resolve) {
      this.chromy.click('[ng-click="modalContract(\'' + param + '\')"]');
      resolve(this.waitForModal('job-contract'));
    }.bind(this));
  },

  /**
   * Opens the modal for creating a new contract
   *
   * @return {Promise} resolves with the job contract modal object
   */
  openNewContractModal: function () {
    return new Promise(function (resolve) {
      this.chromy.click('.hrjc-btn-add-contract > .btn-primary');
      resolve(this.waitForModal('job-contract'));
    }.bind(this));
  },

  /**
   * Overrides the original tab's `waitForReady` method
   * There is no single selector that can be used as `readySelector` (which
   * would be used by the original `waitForReady` method) to detect when the
   * tab is ready, so as a quick workaround we simply override the method
   * and perform all the needed checks in it
   */
  waitForReady: function () {
    this.chromy.waitUntilVisible('.hrjc-summary');
    this.chromy.wait(function () {
      // = waitWhileVisible
      var dom = document.querySelector('.hrjc-list-contract .spinner');
      return dom === null || (dom.offsetWidth <= 0 && dom.offsetHeight <= 0);
    });

    this.chromy.wait(500);
  },

  /**
   * Shows the full history of a contract
   *
   * @return {object}
   */
  showFullHistory: function () {
    this.chromy.click('[heading="Full History"] > a');
    this.chromy.wait('.hrjc-context-menu-toggle');
  }
});
