const Page = require('../page');

module.exports = class Modal extends Page {
  constructor () {
    super(...arguments);
    this.modalRoot = '.modal';
  }
};
