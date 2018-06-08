const SSP = require('./ssp');

module.exports = class SSPVacancies extends SSP {
  /**
   * Opens More Details section
   */
  async showMoreDetails () {
    await this.puppet.click('.fieldset-title');
    await this.puppet.waitFor(2000); // wait for animation to complete
  }
};
