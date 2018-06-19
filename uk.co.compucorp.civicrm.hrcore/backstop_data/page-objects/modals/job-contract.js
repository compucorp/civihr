/* global XPathResult */

const Modal = require('./modal');

module.exports = class JobContractModal extends Modal {
  /**
   * Selects the tab with the given title
   *
   * @param {String} tabTitle
   */
  async selectTab (tabTitle) {
    await this.puppet.evaluate(function (tabTitle) {
      // = clickLabel
      const xPath = './/a[text()="' + tabTitle + '"]';
      const link = document.evaluate(xPath, document.body, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;

      link.click();
    }, tabTitle);
  }
};
