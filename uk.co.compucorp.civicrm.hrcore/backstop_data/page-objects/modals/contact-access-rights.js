const modal = require('./modal');

module.exports = modal.extend({
  /**
   * Opens a ui-select dropdown
   *
   * @return {object}
   */
  async openDropdown (name) {
    const common = '[ng-model="modalCtrl.selectedData.%{name}"] input';

    await this.puppet.click(common.replace('%{name}', name));
    await this.puppet.waitFor(100);
  }
});
