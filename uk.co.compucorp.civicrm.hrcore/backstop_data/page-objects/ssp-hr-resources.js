const modal = require('./page');

module.exports = modal.extend({
  /**
   * Opens See Resources section
   *
   * @return {object}
   */
  async seeResources () {
    await this.puppet.click('.fieldset-title');
    await this.puppet.waitFor(2000); // wait for animation to complete
  }
});
