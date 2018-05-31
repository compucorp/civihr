const Page = require('../page');

module.exports = class Modal extends Page {
  constructor () {
    super(...arguments);
    this.modalRoot = '.modal';
  }

  async waitForReady () {
    await this.puppet.waitFor('.modal-body', { visible: true });
  }
};
