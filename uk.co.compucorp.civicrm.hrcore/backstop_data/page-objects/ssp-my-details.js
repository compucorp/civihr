const modal = require('./page');

module.exports = modal.extend({
  /**
   * Opens Edit My Details Popup
   *
   */
  async showEditMyDetailsPopup () {
    await this.puppet.click('[href="/my_details/nojs/view"]');
    await this.puppet.waitFor('.modal-civihr-custom__section', { visible: true });
  }
});
