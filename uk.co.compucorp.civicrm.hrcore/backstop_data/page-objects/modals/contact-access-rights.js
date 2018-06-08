const Modal = require('./modal');

module.exports = class ContactAccessRights extends Modal {
  /**
   * Opens a ui-select dropdown
   */
  async openDropdown (name) {
    const common = '[ng-model="modalCtrl.selectedData.%{name}"] input';

    await this.puppet.click(common.replace('%{name}', name));
    await this.puppet.waitFor(100);
  }
};
