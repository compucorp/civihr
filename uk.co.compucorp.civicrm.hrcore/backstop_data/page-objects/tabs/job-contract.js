const Tab = require('./tab');

module.exports = class JobContractTab extends Tab {
  constructor () {
    super(...arguments);
    this.tabTitle = 'Job Contract';
  }

  /**
   * Clicks on the delete button
   */
  async attemptDelete () {
    await this.puppet.click('.hrjc-list-contract-item:nth-child(1) .btn-danger');
    await this.waitForModal();
  }

  /**
   * Opens the modal of an already existing contract
   *
   * @param {String} mode "correct" or "revision"
   * @return {Object} the job contract modal object
   */
  async openContractModal (mode) {
    const param = mode === 'correct' ? 'edit' : (mode === 'revision' ? 'change' : '');

    await this.puppet.click('[ng-click="modalContract(\'' + param + '\')"]');

    return this.waitForModal('job-contract');
  }

  /**
   * Opens the modal for creating a new contract
   *
   * @return {Object} the job contract modal object
   */
  async openNewContractModal () {
    await this.puppet.click('.hrjc-btn-add-contract > .btn-primary');

    return this.waitForModal('job-contract');
  }

  /**
   * Overrides the original tab's `waitForReady` method
   * There is no single selector that can be used as `readySelector` (which
   * would be used by the original `waitForReady` method) to detect when the
   * tab is ready, so as a quick workaround we simply override the method
   * and perform all the needed checks in it
   */
  async waitForReady () {
    await this.puppet.waitFor('.hrjc-summary', { visible: true });
    await this.puppet.waitFor('.hrjc-list-contract .spinner', { hidden: true });
    await this.puppet.waitFor(500);
  }

  /**
   * Shows the full history of a contract
   */
  async showFullHistory () {
    await this.puppet.click('[heading="Full History"] > a');
    await this.puppet.waitFor('.hrjc-context-menu-toggle');
  }
};
