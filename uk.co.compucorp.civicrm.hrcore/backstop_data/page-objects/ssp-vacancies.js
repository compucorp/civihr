const modal = require('./page');

module.exports = modal.extend({
  /**
   * Opens More Details section
   */
  async showMoreDetails () {
    await this.puppet.click('.fieldset-title');
    await this.puppet.waitFor(2000);
  }
});
