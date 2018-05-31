const Modal = require('./modal');

module.exports = class SSPLeaveRequestModal extends Modal {
  /**
   * Selects tabs like comments or attachments
   *
   * @param {String} tabName like comments or attachments
   */
  async selectTab (tabName) {
    await this.puppet.click('div.chr_leave-request-modal__tab li[heading=\'' + tabName + '\'] a');
  }
};
