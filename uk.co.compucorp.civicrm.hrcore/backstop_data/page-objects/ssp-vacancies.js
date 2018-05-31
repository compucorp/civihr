const Page = require('./page');

module.exports = class SSPVacancies extends Page {
  /**
   * Opens More Details section
   */
  async showMoreDetails () {
    await this.puppet.click('.fieldset-title');
    await this.puppet.waitFor(2000); // wait for animation to complete
  }
};
