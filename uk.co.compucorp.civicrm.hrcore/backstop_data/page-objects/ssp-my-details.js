const SSP = require('./ssp');

module.exports = class SSPMyDetails extends SSP {
  /**
   * Opens Edit My Details Popup
   *
   */
  async showEditMyDetailsPopup () {
    await this.puppet.click('[href="/my_details/nojs/view"]');
    await this.puppet.waitFor('.modal-civihr-custom__section', { visible: true });
  }
};
