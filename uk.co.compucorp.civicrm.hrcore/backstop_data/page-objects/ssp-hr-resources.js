const SSP = require('./ssp');

module.exports = class SSPHRResources extends SSP {
  /**
   * Opens See Resources section
   */
  async seeResources () {
    await this.puppet.click('.fieldset-title');
    await this.puppet.waitFor(2000); // wait for animation to complete
  }
};
